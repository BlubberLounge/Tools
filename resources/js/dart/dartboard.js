import * as d3 from 'd3';

function _asPercent(number) {
    return (number / 100).toFixed(2)
}

function _createBoardSvg(board) {
    return board.element.append('svg')
        .attr('width', board.width)
        .attr('height', board.height)
        .append('g')
        .attr('transform', `translate(${board.radius}, ${board.radius}) rotate(${board.rotation})`)
}

function _addRingToBeds(ring, beds) {
    const bedsWithRings = []
    beds.forEach(bed => {
        let bedWithRing = JSON.parse(JSON.stringify(bed))
        bedWithRing.ring = ring
        bedsWithRings.push(bedWithRing)
    })

    return bedsWithRings
}

function _dispatchThrowEvent(boardContainer, bed, event)
{
    // // e = Mouse click event. = Mouse click event.
    let rect = document.getElementById('dartboard').getBoundingClientRect();
    let x = event.clientX - rect.left; //x position within the element.
    let y = event.clientY - rect.top;  //y position within the element.

    let div = document.createElement("i");
    div.style.position = "absolute";
    div.style.setProperty("top", y+"px");
    div.style.setProperty("left", x+"px");
    div.style.width = "10px";
    div.style.height = "10px";
    div.style.borderRadius = "50%";
    div.style.backgroundColor = "#0396a3";
    // div.classList.add('fa-solid', 'fa-x', 'fa-lg'); // fa-xmark
    // div.style.color = "blue";
    // div.style.fontWeight = "bold";

    document.getElementById('dartboard').append(div);

    const detail = {
        bed: bed.ring.abbr + bed.frame,
        ring: bed.ring.name,
        score: bed.frame * bed.ring.multiplier,
        x: x,
        y: y
    };

    boardContainer.dispatchEvent(new CustomEvent('throw', { detail }))
}

function _renderSegments(board, ring, beds, outerRadius, innerRadius) {
    const classname = `c-Dartboard-${ring.name}`

    const segments = board.svg.append('g')
        .classed(classname, true)
        .selectAll('arc')
            .data(board.pie(_addRingToBeds(ring, beds)))
            .enter()
                .append('g')
                    .attr('class', bed => `c-Dartboard-bed ${classname}--${bed.data.frame} is${bed.data.color}`)
                    .on('click', (event, bed) => _dispatchThrowEvent(board.container, bed.data, event))

    segments.append('path').attr('d', d3.arc().outerRadius(outerRadius).innerRadius(innerRadius))
}

function _renderBorders(board, ring, beds, outerRadius, innerRadius) {
    const borderArc = d3.arc().outerRadius(outerRadius).innerRadius(innerRadius)
    const borderSegments = board.svg.append('g')
        .classed('c-Dartboard-borders', true)
        .selectAll('arc')
            .data(board.pie(_addRingToBeds(ring, beds)))
            .enter()
                .append('g')
                    .attr('class', bed => `c-Dartboard-border c-Dartboard-border--${bed.data.frame}`)
                    .on('click', (event, bed) => _dispatchThrowEvent(board.container, bed.data, event))
    borderSegments.append('path').attr('d', borderArc)

    function _determineRotation(bedData) {
        return -board.rotation + (board.segmentWidth * (bedData.position - 1))
    }
    function determineX(bed) {
        return borderArc.centroid(bed)[0]
    }
    function determineY(bed) {
        return borderArc.centroid(bed)[1]
    }

    borderSegments.append('text')
        .classed('c-Dartboard-borderLabel', true)
        .attr('x', determineX)
        .attr('y', determineY)
        .attr('dy', '.35em')
        .attr('transform', bed => `rotate(${_determineRotation(bed.data)}, ${determineX(bed)}, ${determineY(bed)})`)
        .attr('text-anchor', 'middle')
        .text(bed => bed.data.frame)
}

class Dartboard
{
    constructor(containerSelector = null)
    {
        this.options = {
            size: null,
            borderPercent: 9,
            doublePercent: 9,
            outerSinglePercent: 29,
            triplePercent: 8,
            innerSinglePercent: 32,
            outerBullPercent: 7,
            innerBullPercent: 6,
        };

        this.rings = {
            BORDER: { name: 'border', abbr: 'M', multiplier: 0 },
            DOUBLE: { name: 'double', abbr: 'D', multiplier: 2 },
            OUTER_SINGLE: { name: 'outerSingle', abbr: 'S', multiplier: 1 },
            TRIPLE: { name: 'triple', abbr: 'T', multiplier: 3 },
            INNER_SINGLE: { name: 'innerSingle', abbr: 'S', multiplier: 1 },
            OUTER_BULL: { name: 'outerBull', abbr: 'B', multiplier: 1 },
            INNER_BULL: { name: 'innerBull', abbr: 'DB', multiplier: 1 },
        };

        this.beds = [
            { frame: 20, position: 1, color: 'Dark' },
            { frame: 1, position: 2, color: 'Light' },
            { frame: 18, position: 3, color: 'Dark' },
            { frame: 4, position: 4, color: 'Light' },
            { frame: 13, position: 5, color: 'Dark' },
            { frame: 6, position: 6, color: 'Light' },
            { frame: 10, position: 7, color: 'Dark' },
            { frame: 15, position: 8, color: 'Light' },
            { frame: 2, position: 9, color: 'Dark' },
            { frame: 17, position: 10, color: 'Light' },
            { frame: 3, position: 11, color: 'Dark' },
            { frame: 19, position: 12, color: 'Light' },
            { frame: 7, position: 13, color: 'Dark' },
            { frame: 16, position: 14, color: 'Light' },
            { frame: 8, position: 15, color: 'Dark' },
            { frame: 11, position: 16, color: 'Light' },
            { frame: 14, position: 17, color: 'Dark' },
            { frame: 9, position: 18, color: 'Light' },
            { frame: 12, position: 19, color: 'Dark' },
            { frame: 5, position: 20, color: 'Light' },
        ];

        const boardContainer = document.querySelector(containerSelector);
        const board = {
            container: boardContainer,
            element: d3.select(containerSelector).append('div').classed('c-Dartboard', true),
            width: this.options.size || Math.min(boardContainer.offsetHeight, boardContainer.offsetWidth),
            segmentWidth: 360 / this.beds.length,
        };
        board.height = board.width;
        board.radius = board.width / 2;
        board.rotation = board.segmentWidth / -2; // rotate so center of first segment is on top
        board.svg = _createBoardSvg(board);
        this.board = board;

        this.sizes = {
            border: board.radius * _asPercent(this.options.borderPercent),
            double: board.radius * _asPercent(this.options.doublePercent),
            outerSingle: board.radius * _asPercent(this.options.outerSinglePercent),
            triple: board.radius * _asPercent(this.options.triplePercent),
            innerSingle: board.radius * _asPercent(this.options.innerSinglePercent),
            outerBull: board.radius * _asPercent(this.options.outerBullPercent),
            innerBull: board.radius * _asPercent(this.options.innerBullPercent),
        };
    }

    render()
    {
        this.board.pie = d3.pie()
            .sort((a, b) => (a.position - b.position))
            .value(() => this.board.segmentWidth);

        let innerRadius = 0;
        let outerRadius = innerRadius + this.sizes.innerBull;
        _renderSegments(this.board, this.rings.INNER_BULL, [{ frame: 50, color: 'Dark' }],
            outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.outerBull;
        _renderSegments(this.board, this.rings.OUTER_BULL, [{ frame: 25, color: 'Light' }],
            outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.innerSingle;
        _renderSegments(this.board, this.rings.INNER_SINGLE, this.beds, outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.triple;
        _renderSegments(this.board, this.rings.TRIPLE, this.beds, outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.outerSingle;
        _renderSegments(this.board, this.rings.OUTER_SINGLE, this.beds, outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.double;
        _renderSegments(this.board, this.rings.DOUBLE, this.beds, outerRadius, innerRadius);

        innerRadius = this.board.radius - this.sizes.border;
        outerRadius = this.board.radius;
        _renderBorders(this.board, this.rings.BORDER, this.beds, outerRadius, innerRadius);
    }
}

export {
    Dartboard as default,
}
