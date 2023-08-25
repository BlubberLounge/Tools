import * as d3 from 'd3';

import * as UTILS from '../utils';
import DartDefinition from './dartDefinition';

import Hit from './game/classes/hit';

/**
 *
 *
 */
export default class Dartboard
{
    constructor(containerSelector = null, settings = {})
    {
        this.identifier = document.getElementById('gameId').getAttribute('value');

        this.locked = false;

        // this.options = {
        //     size: 'size' in settings ? settings.size : 0,
        //     borderPercent: 13,
        //     doublePercent: 8,
        //     outerSinglePercent: 28,
        //     triplePercent: 7,
        //     innerSinglePercent: 32,
        //     outerBullPercent: 6,
        //     innerBullPercent: 6,
        // };

        this.options = {
            size: 'size' in settings ? settings.size : 0,
            borderPercent: .12,
            doublePercent: DartDefinition.ringRadiiRelative.double - .02,
            outerSinglePercent: DartDefinition.ringRadiiRelative.outerSingle - .02,
            triplePercent: DartDefinition.ringRadiiRelative.tripple - .02,
            innerSinglePercent: DartDefinition.ringRadiiRelative.innerSingle - .05,
            outerBullPercent: DartDefinition.ringRadiiRelative.bullseye - .01,
            innerBullPercent: DartDefinition.ringRadiiRelative.doubleBullseye,
        };

        this.rings = {
            BORDER: { name: 'border', abbr: 'O', multiplier: 0 },
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
        board.svg = this._createBoardSvg(board);
        this.board = board;

        this.sizes = {
            border: board.radius * this.options.borderPercent,
            double: board.radius * this.options.doublePercent,
            outerSingle: board.radius * this.options.outerSinglePercent,
            triple: board.radius * this.options.triplePercent,
            innerSingle: board.radius * this.options.innerSinglePercent,
            outerBull: board.radius * this.options.outerBullPercent,
            innerBull: board.radius * this.options.innerBullPercent,
        };
    }

    render()
    {
        let skeletonDartboard = document.getElementById('skeleton-dartboard');
        if(skeletonDartboard)
            skeletonDartboard.remove();

        this.board.pie = d3.pie()
            .sort((a, b) => (a.position - b.position))
            .value(() => this.board.segmentWidth);

        let innerRadius = 0;
        let outerRadius = innerRadius + this.sizes.innerBull;
        this._renderSegments(this.rings.INNER_BULL, [{ frame: 50, color: 'Dark' }], outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.outerBull;
        this._renderSegments(this.rings.OUTER_BULL, [{ frame: 25, color: 'Light' }], outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.innerSingle;
        this._renderSegments(this.rings.INNER_SINGLE, this.beds, outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.triple;
        this._renderSegments(this.rings.TRIPLE, this.beds, outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.outerSingle;
        this._renderSegments(this.rings.OUTER_SINGLE, this.beds, outerRadius, innerRadius);

        innerRadius = outerRadius;
        outerRadius = innerRadius + this.sizes.double;
        this._renderSegments(this.rings.DOUBLE, this.beds, outerRadius, innerRadius);

        innerRadius = this.board.radius - this.sizes.border;
        outerRadius = this.board.radius;
        this._renderBorders(this.rings.BORDER, this.beds, outerRadius, innerRadius);

        sessionStorage.setItem(this.identifier, true);
    }

    _handleClick(event, bed)
    {
        const bedData = bed.data;
        // // e = Mouse click event. = Mouse click event.
        const rect = document.getElementsByClassName('c-Dartboard')[0].getBoundingClientRect();
        const x = event.clientX - rect.left - this.options.borderPercent; // x position within the element.
        const y = event.clientY - rect.top - this.options.borderPercent;  // y position within the element.

        const points = bedData.frame * bedData.ring.multiplier;
        const field = bedData.frame;
        const fieldName = bedData.ring.abbr + bedData.frame;
        const ring = bedData.ring.abbr;
        const ringName = bedData.ring.name;

        const hit = new Hit(x, y, points, field, fieldName, ring, ringName);

        // console.log(hit);

        // dartHit event
        this._dispatchThrowEvent(hit);
    }

    _dispatchThrowEvent(hit)
    {
        this.board.container.dispatchEvent(new CustomEvent('dartHit',
        {
            detail: hit,
        }));
    }

    _renderSegments(ring, beds, outerRadius, innerRadius)
    {
        const classname = `c-Dartboard-${ring.name}`;
        const board = this.board;

        const segments = board.svg.append('g')
            .classed(classname, true)
            .selectAll('arc')
                .data(board.pie(this._addRingToBeds(ring, beds)))
                .enter()
                    .append('g')
                        .attr('class', bed => `c-Dartboard-bed ${classname}--${bed.data.frame} is${bed.data.color} fade-in`)
                        .attr('style', bed => `animation-delay:`+ (this._animate() ? (bed.data.position != undefined ? bed.data.position * 100 : 2000) : 0 ) +`ms;`)
                        .on('click', (event, bed) => this._handleClick(event, bed))

        segments.append('path').attr('d', d3.arc().outerRadius(outerRadius).innerRadius(innerRadius))
    }

    _renderBorders(ring, beds, outerRadius, innerRadius)
    {
        const borderArc = d3.arc().outerRadius(outerRadius).innerRadius(innerRadius);
        const board = this.board;

        const borderSegments = board.svg.append('g')
            .classed('c-Dartboard-borders', true)
            .selectAll('arc')
                .data(board.pie(this._addRingToBeds(ring, beds)))
                .enter()
                    .append('g')
                        .attr('class', bed => `c-Dartboard-border c-Dartboard-border--${bed.data.frame} fade-in`)
                        .attr('style', bed => `animation-delay: `+ (this._animate() ? bed.data.position * 100 : 0 ) +`ms;`)
                        .on('click', (event, bed) => this._handleClick(event, bed))
        borderSegments.append('path').attr('d', borderArc)

        var _determineRotation = (bedData) => {
            return -this.board.rotation + (this.board.segmentWidth * (bedData.position - 1))
        };
        var determineX = (bed) => {
            return borderArc.centroid(bed)[0]
        }
        var determineY = (bed) => {
            return borderArc.centroid(bed)[1]
        }

        borderSegments.append('text')
            .classed('c-Dartboard-borderLabel', true)
            .attr('x', determineX)
            .attr('y', determineY)
            .attr('dy', '.35em')
            // .attr('transform', bed => `rotate(${_determineRotation(bed.data)}, ${determineX(bed)}, ${determineY(bed)})`)
            .attr('transform', bed => `rotate(9, ${determineX(bed)}, ${determineY(bed)})`)
            .attr('text-anchor', 'middle')
            .text(bed => bed.data.frame)
    }

    _createBoardSvg(board)
    {
        return board.element.append('svg')
            .attr('width', board.width)
            .attr('height', board.height)
            .append('g')
            .attr('transform', `translate(${board.radius}, ${board.radius}) rotate(${board.rotation})`)
    }

    _addRingToBeds(ring, beds)
    {
        const bedsWithRings = []
        beds.forEach(bed => {
            let bedWithRing = JSON.parse(JSON.stringify(bed))
            bedWithRing.ring = ring
            bedsWithRings.push(bedWithRing)
        })

        return bedsWithRings
    }

    _animate()
    {
        return sessionStorage.getItem(this.identifier) ? false : true;
    }

    isLocked()
    {
        return this.locked;
    }

    lock()
    {
        this.locked = true;
    }

    unlock()
    {
        this.locked = false;
    }
}
