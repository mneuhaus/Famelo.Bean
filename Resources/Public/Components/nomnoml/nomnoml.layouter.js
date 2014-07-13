var nomnoml = nomnoml || {}

nomnoml.Classifier = function (type, name, compartments){
	return {
        type: type,
        name: name,
        compartments: compartments
    }
}
nomnoml.Compartment = function (lines, nodes, relations){
	return {
        lines: lines,
        nodes: nodes,
        relations: relations
    }
}

nomnoml.layout = function (measurer, config, ast){
	function runDagre(input){
		return dagre.layout()
					.rankSep(config.spacing)
					.nodeSep(config.spacing)
					.edgeSep(config.spacing)
					.rankDir(config.direction)
					.run(input)
	}
	function measureLines(lines, fontWeight){
		if (!lines.length)
			return { width: 0, height: config.padding }
		measurer.setFont(config, fontWeight)
		return {
			width: Math.round(_.max(_.map(lines, measurer.textWidth)) + 2*config.padding),
			height: Math.round(measurer.textHeight() * lines.length + 2*config.padding)
		}
	}

	function findPropertyCoordinates(node, name) {
		var compartment = node.compartments[1];

		for (var i = 0; i < compartment.lines.length; i++) {
			var propertyName = compartment.lines[i].split(':')[0];
			// console.log(propertyName, name);
			// ((0.5+(i+.5)*config.leading)*config.fontSize)
			if (propertyName == name) {
				return {
					x: 1,
					y: 1
				}
			}
		};
	}

	function layoutCompartment(c, compartmentIndex){
		var textSize = measureLines(c.lines, compartmentIndex ? 'normal' : 'bold')
		c.width = textSize.width
		c.height = textSize.height

		if (!c.nodes.length && !c.relations.length)
			return

		_.each(c.nodes, layoutClassifier)

		var g = new dagre.Digraph()
		_.each(c.nodes, function (e){
			g.addNode(e.name, { width: e.width, height: e.height })
		})
		_.each(c.relations, function (r){
			g.addEdge(r.id, r.start, r.end)
		})
		var dLayout = runDagre(g)

		var rels = _.indexBy(c.relations, 'id')
		var nodes = _.indexBy(c.nodes, 'name')
		function toPoint(o){ return {x:o.x, y:o.y} }
		dLayout.eachNode(function(u, value) {
			nodes[u].x = value.x
			nodes[u].y = value.y
		})
		dLayout.eachEdge(function(e, u, v, value) {
			var start = _.clone(nodes[u]), end = _.clone(nodes[v]);
			var startCoordinates = findPropertyCoordinates(start, rels[e].startProperty);
			var endCoordinates = findPropertyCoordinates(end, rels[e].endProperty);

			start.x = startCoordinates.x
			start.y = startCoordinates.y
			if (typeof(endCoordinates) == 'object') {
				end.x = endCoordinates.y
				end.y = endCoordinates.y
			}
			// console.log(startCoordinates);
			rels[e].path = _.map(_.flatten([start, value.points, end]), toPoint)
			// console.log(rels[e].path)
		})
		var graph = dLayout.graph()
		var graphHeight = graph.height ? graph.height + 2*config.gutter : 0
		var graphWidth = graph.width ? graph.width + 2*config.gutter : 0

		c.width = Math.max(textSize.width, graphWidth) + 2*config.padding
		c.height = textSize.height + graphHeight + config.padding
	}
	function layoutClassifier(clas){
		_.each(clas.compartments, layoutCompartment)
		clas.width = _.max(_.pluck(clas.compartments, 'width'))
		clas.height = _.sum(clas.compartments, 'height')
		clas.x = clas.width/2
		clas.y = clas.height/2
		_.each(clas.compartments, function(co){ co.width = clas.width })
	}
	layoutCompartment(ast)
	return ast
}