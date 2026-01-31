/**
 * @author Maximilian Mewes
 *
 *
 */

import Plotly from 'plotly.js-dist';

import * as UTILS from '../utils';
import Dartboard from './dartboard';
import DartCalculator from './dartCalculator';
import DartDefinition from './dartDefinition';

// Global field order state
let currentFieldOrder = [20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5];

document.addEventListener('DOMContentLoaded', function()
{
    // Setup field order input listener
    setupFieldOrderInput();

    // Render interactive dartboards
    renderInfoDartboards();

    // optimazation: put in seperate file and load sync
    fetchDataRenderPlots();


    var polarArr = DartDefinition.fieldOrder20;
    polarArr.push(20);

    const data = [{
        r: polarArr,
        theta: polarArr.map( (f, i) => ((DartDefinition.fieldSizeDeg * i) > 360 ? 0 : DartDefinition.fieldSizeDeg * i)),
        fill: 'toself',
        type: 'scatterpolar',
    }];

    const layout = {
        polar: {
          radialaxis: {
            visible: true,
            range: [0, 22],
            angle: 90,
            tickangle: 90
          },
          angularaxis: {
            direction: "clockwise",
            dtick: 360 / 20
          }
        },
        showlegend: false,
        paper_bgcolor: 'rgba(0, 0, 0, 0)',
        plot_bgcolor: 'rgba(0, 0, 0, 0)',
    };

    const config = {
        displayModeBar: false, // this is the line that hides the bar.
    };

    Plotly.newPlot("graph01", data, layout, config);

});

function renderPlot(values)
{
    values = values.filter( (value, i) => !(value.sigma % 1) );

    const data = [{
        x: values.map( value => value.sigma),
        y: values.map( value => value.score),
        type: 'scatter',
        mode: 'lines+markers',
        line: {shape: 'spline'},
    }];

    const layout = {
        title: 'Expected Score Curve',
        xaxis: {
            type: 'log',
            title: 'Standard Deviation in mm',
            fixedrange: true,
            linecolor: 'rgba(255, 255, 255, .25)',
            gridcolor: 'rgba(255, 255, 255, .25)',
            zerolinecolor: 'rgba(255, 255, 255, .75)',
            tickfont: {
                color: 'rgba(255, 255, 255, .5)'
            },
        },
        yaxis: {
            title: 'Expected score',
            fixedrange: true,
            linecolor: 'rgba(255, 255, 255, .25)',
            gridcolor: 'rgba(255, 255, 255, .25)',
            zerolinecolor: 'rgba(255, 255, 255, .75)',
            tickfont: {
                color: 'rgba(255, 255, 255, .5)'
            },
        },
        annotations: [
            {
                x: 1.2,
                y: 20.6,
                xref: 'x',
                yref: 'y',
                text: 'T20 / T19',
                showarrow: true,
                arrowhead: 3,
                arrowcolor: 'rgba(255, 255, 255, .5)',
                ax: 30,
                ay: -40,
                font: {
                    size: 16,
                    color: 'rgba(255, 255, 255, .75)'
                },
            },
        ],
        paper_bgcolor: 'rgba(0, 0, 0, 0)',
        plot_bgcolor: 'rgba(0, 0, 0, 0)',
    }; // It's a stub, put layout config here.

    const config = {
        displayModeBar: false, // this is the line that hides the bar.
    };

    Plotly.newPlot('expectationDataGraph', data, layout, config);
}

function fetchDataRenderPlots()
{
    axios.get('/api/v1/dart/expectationData').then( response =>
        {
            let data = response.data.data.data;
            data = data.sort( (a,b) => a.score - b.score );

            renderPlot(data);
            renderPlotDartboard(data);

        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
    });
}

function renderPlotDartboard(expData)
{
    let expData1 = expData.filter( d => d.sigma <= 16 );
    let expData2 = expData.filter( d => d.sigma > 16 );

    let size = 200;
    let matrix = [];
    for(let col = -size; col < size+1; col++) {
        let res = [];
        for(let row = -size; row < size+1; row++) {
            let points = DartCalculator.getScoreCartesian(row, col);
            res.push(points <= 0 ? null : points);
        }
        matrix.push(res);
    }

    let count = -1;
    let randomThrows = DartCalculator.generateRandomThrows(100, 20, size, x => x - size);
    console.table(DartCalculator.calculateStandardDeviation(randomThrows));
    randomThrows.forEach( t => {
        if(t.points == 25 || t.points == 50)
            count++;
    });
    // console.log(JSON.stringify(randomThrows.map( t => {
    //     return {
    //         x: t.x - size,
    //         y: t.y - size,
    //         points: t.points,
    //     };
    // })));
    console.table(`Treffer: ${count} / 100`);
    console.table(`Treffer: ${count / 2} / 50`);

    const data = [
        {
            z: matrix,
            hoverongaps: false,
            type: 'heatmap',
            name: 'scores',
        },{
            x: randomThrows.map( t => t.x ),
            y: randomThrows.map( t => t.y ),
            mode: 'markers',
            type: 'scatter',
            name: 'probability',
            marker: {
                size: 4
            },
        },{
            x: expData1.map( data => (data.x * size*2) + size+1),
            y: expData1.map( data => (data.y * size*2) + size+1),
            mode: 'lines',
            type: 'scatter',
            line: {
                color: 'rgb(0, 255, 0)',
                width: 4
            }
        },{
            x: expData2.map( data => (data.x * size*2) + size+1),
            y: expData2.map( data => (data.y * size*2) + size+1),
            mode: 'lines',
            type: 'scatter',
            line: {
                color: 'rgb(0, 255, 0)',
                width: 4
            }
        },
    ];

    const layout = {
        legend: {
            font: {
                size: 12,
                color: '#fff'
            },
        },
        showlegend: false,
        xaxis: {
            fixedrange: true,
            showgrid: false,
            zeroline: false,
            showline: false,
            autotick: true,
            // ticks: '',
            // showticklabels: false,
            zerolinecolor: '#fff',
            linecolor: '#fff',
            gridcolor: '#fff',
            tickfont: {
                // size: 14,
                color: '#fff'
            },
            rangemode: 'tozero',
        },
        yaxis: {
            fixedrange: true,
            showgrid: false,
            zeroline: false,
            showline: false,
            autotick: true,
            // ticks: '',
            // showticklabels: false,
            zerolinecolor: '#fff',
            linecolor: '#fff',
            gridcolor: '#fff',
            tickfont: {
                // size: 14,
                color: '#fff'
            },
        },
        paper_bgcolor: 'rgba(0, 0, 0, 0)',
        plot_bgcolor: 'rgba(0, 0, 0, 0)',
    };

    const config = {
        displayModeBar: false, // this is the line that hides the bar.
    };

    Plotly.newPlot('dartboardDataGraph', data, layout, config);
}

function renderInfoDartboards()
{
    // Interactive Dartboard
    const interactiveContainer = document.getElementById('interactiveDartboard');
    if (interactiveContainer) {
        const dartboard = new Dartboard('#interactiveDartboard', { size: 340 });
        dartboard.render();

        const tooltip = document.getElementById('dartboardTooltip');
        setupDartboardHover(interactiveContainer, tooltip, (bed) => {
            const points = bed.frame * bed.ring.multiplier;
            return { value: points, label: `${bed.ring.abbr}${bed.frame}` };
        });
    }

    // Field Values Dartboard
    const fieldValuesContainer = document.getElementById('fieldValuesDartboard');
    if (fieldValuesContainer) {
        const dartboard = new Dartboard('#fieldValuesDartboard', { size: 340 });
        dartboard.render();

        const tooltip = document.getElementById('fieldValuesTooltip');
        setupDartboardHover(fieldValuesContainer, tooltip, (bed) => {
            const points = bed.frame * bed.ring.multiplier;
            return { value: points, label: `${bed.ring.abbr}${bed.frame}` };
        });
    }

    // Heatmap Dartboard
    const heatmapContainer = document.getElementById('heatmapDartboard');
    if (heatmapContainer) {
        const dartboard = new Dartboard('#heatmapDartboard', { size: 340 });
        dartboard.render();

        // Apply heatmap styling with current field order
        const heatmapData = JSON.parse(document.getElementById('heatmapData')?.textContent || '[]');
        applyHeatmapStyling(heatmapContainer, heatmapData, currentFieldOrder);

        const tooltip = document.getElementById('heatmapTooltip');
        setupDartboardHover(heatmapContainer, tooltip, (bed, index) => {
            const heat = heatmapData[index]?.[3] ?? 0;
            const percent = Math.round(heat * 100);
            return { value: `${percent}%`, label: `S${bed.frame}` };
        });
    }

    // Throw Heatmap Dartboard with overlay
    const throwHeatmapContainer = document.getElementById('throwHeatmapDartboard');
    if (throwHeatmapContainer) {
        const dartboard = new Dartboard('#throwHeatmapDartboard', { size: 340 });
        dartboard.render();

        // Make dartboard slightly transparent to see heatmap
        const dartboardSvg = throwHeatmapContainer.querySelector('.c-Dartboard');
        if (dartboardSvg) {
            dartboardSvg.style.opacity = '0.7';
        }

        // Initialize heatmap.js overlay
        renderThrowHeatmap();
    }

    // Strategic Dartboard
    const strategicContainer = document.getElementById('strategicDartboard');
    if (strategicContainer) {
        const dartboard = new Dartboard('#strategicDartboard', { size: 340 });
        dartboard.render();

        // Apply strategic heatmap styling with current field order
        const strategicData = JSON.parse(document.getElementById('strategicData')?.textContent || '[]');
        const strategicHeatData = JSON.parse(document.getElementById('strategicHeatData')?.textContent || '[]');
        applyStrategicStyling(strategicContainer, strategicHeatData, currentFieldOrder);

        const tooltip = document.getElementById('strategicTooltip');
        setupDartboardHover(strategicContainer, tooltip, (bed, index) => {
            const avg = strategicData[index]?.[3] ?? 0;
            return { value: avg.toFixed(1), label: `near ${bed.frame}` };
        });
    }
}

function setupDartboardHover(container, tooltip, getInfo)
{
    if (!container || !tooltip) return;

    const beds = container.querySelectorAll('.c-Dartboard-bed, .c-Dartboard-border');
    const valueEl = tooltip.querySelector('.tooltip-value');
    const labelEl = tooltip.querySelector('.tooltip-label');

    // Map segment frames to indices
    const frameOrder = [20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5];

    beds.forEach(bed => {
        bed.addEventListener('mouseenter', () => {
            // Extract bed info from class names
            const classList = bed.className.baseVal || bed.className;
            const frameMatch = classList.match(/--(\d+)/);
            const frame = frameMatch ? parseInt(frameMatch[1]) : 0;

            // Determine ring type
            let ring = { name: 'single', abbr: 'S', multiplier: 1 };
            if (classList.includes('double')) ring = { name: 'double', abbr: 'D', multiplier: 2 };
            else if (classList.includes('triple') || classList.includes('Triple')) ring = { name: 'triple', abbr: 'T', multiplier: 3 };
            else if (classList.includes('innerBull')) ring = { name: 'innerBull', abbr: 'DB', multiplier: 1 };
            else if (classList.includes('outerBull')) ring = { name: 'outerBull', abbr: 'B', multiplier: 1 };

            const bedData = { frame, ring };
            const index = frameOrder.indexOf(frame);
            const info = getInfo(bedData, index >= 0 ? index : 0);

            if (valueEl) valueEl.textContent = info.value;
            if (labelEl) labelEl.textContent = info.label;
            tooltip.classList.add('visible');
        });

        bed.addEventListener('mouseleave', () => {
            tooltip.classList.remove('visible');
        });
    });
}

function parseFieldOrder(input)
{
    const numbers = input.split(',').map(s => parseInt(s.trim(), 10)).filter(n => !isNaN(n));
    const unique = [...new Set(numbers)];
    const valid = unique.filter(n => n >= 1 && n <= 20);
    return valid.length === 20 ? valid : null;
}

function debounce(func, wait)
{
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function setupFieldOrderInput()
{
    const pillsContainer = document.getElementById('fieldOrderPills');
    const applyBtn = document.getElementById('applyFieldOrderBtn');
    const resetBtn = document.getElementById('resetFieldOrderBtn');

    if (!pillsContainer || !applyBtn) return;

    let draggedPill = null;

    // Setup drag and drop for pills
    const pills = pillsContainer.querySelectorAll('.field-pill');

    pills.forEach(pill => {
        pill.addEventListener('dragstart', (e) => {
            draggedPill = pill;
            pill.classList.add('dragging');
            pillsContainer.classList.add('drag-active');
            e.dataTransfer.effectAllowed = 'move';
        });

        pill.addEventListener('dragend', () => {
            pill.classList.remove('dragging');
            pillsContainer.classList.remove('drag-active');
            pills.forEach(p => p.classList.remove('drag-over'));
            draggedPill = null;
        });

        pill.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (pill !== draggedPill) {
                pill.classList.add('drag-over');
            }
        });

        pill.addEventListener('dragleave', () => {
            pill.classList.remove('drag-over');
        });

        pill.addEventListener('drop', (e) => {
            e.preventDefault();
            pill.classList.remove('drag-over');

            if (draggedPill && draggedPill !== pill) {
                const allPills = [...pillsContainer.querySelectorAll('.field-pill')];
                const draggedIndex = allPills.indexOf(draggedPill);
                const targetIndex = allPills.indexOf(pill);

                if (draggedIndex < targetIndex) {
                    pill.parentNode.insertBefore(draggedPill, pill.nextSibling);
                } else {
                    pill.parentNode.insertBefore(draggedPill, pill);
                }
            }
        });
    });

    // Apply button
    applyBtn.addEventListener('click', () => {
        const newOrder = getFieldOrderFromPills();

        if (newOrder && newOrder.length === 20) {
            currentFieldOrder = newOrder;

            // Re-apply styling to all dartboards
            updateAllDartboards();

            // Show success feedback
            applyBtn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
            applyBtn.classList.add('bg-green-500');
            applyBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Applied';
            setTimeout(() => {
                applyBtn.classList.remove('bg-green-500');
                applyBtn.classList.add('bg-orange-500', 'hover:bg-orange-600');
                applyBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Apply';
            }, 1500);
        }
    });

    // Reset button
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            const defaultOrder = [20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5];
            const pills = pillsContainer.querySelectorAll('.field-pill');

            // Sort pills back to default order
            defaultOrder.forEach(num => {
                const pill = [...pills].find(p => parseInt(p.dataset.value) === num);
                if (pill) {
                    pillsContainer.appendChild(pill);
                }
            });

            // Apply default order
            currentFieldOrder = defaultOrder;
            updateAllDartboards();
        });
    }
}

function getFieldOrderFromPills()
{
    const pillsContainer = document.getElementById('fieldOrderPills');
    if (!pillsContainer) return null;

    const pills = pillsContainer.querySelectorAll('.field-pill');
    return [...pills].map(pill => parseInt(pill.dataset.value));
}

function updateAllDartboards()
{
    console.log('Updating dartboards with order:', currentFieldOrder);

    // Update Heatmap Dartboard
    const heatmapContainer = document.getElementById('heatmapDartboard');
    if (heatmapContainer) {
        const heatmapData = JSON.parse(document.getElementById('heatmapData')?.textContent || '[]');
        console.log('Heatmap data:', heatmapData);
        applyHeatmapStyling(heatmapContainer, heatmapData, currentFieldOrder);
    }

    // Update Strategic Dartboard
    const strategicContainer = document.getElementById('strategicDartboard');
    if (strategicContainer) {
        const strategicHeatData = JSON.parse(document.getElementById('strategicHeatData')?.textContent || '[]');
        console.log('Strategic data:', strategicHeatData);
        applyStrategicStyling(strategicContainer, strategicHeatData, currentFieldOrder);
    }
}

function applyHeatmapStyling(container, heatmapData, customOrder = null)
{
    const defaultOrder = [20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5];
    const frameOrder = customOrder || defaultOrder;

    // Create a mapping: for each frame number, find its position index in the new order
    const frameToPositionIndex = {};
    frameOrder.forEach((frame, positionIndex) => {
        frameToPositionIndex[frame] = positionIndex;
    });

    // Use position-based heat - position 0 (top) is hottest, decreasing around
    const getHeatForPosition = (posIdx) => {
        const distFromTop = Math.min(posIdx, 20 - posIdx);
        return 1 - (distFromTop / 10);
    };

    // Select all bed elements and apply styling based on their frame number
    const allBeds = container.querySelectorAll('.c-Dartboard-bed');
    console.log(`Found ${allBeds.length} total beds`);

    allBeds.forEach(bed => {
        // Extract frame number from class name (e.g., "c-Dartboard-innerSingle--20" -> 20)
        const classMatch = bed.className.baseVal?.match(/--(\d+)/) || bed.className?.match?.(/--(\d+)/);
        if (classMatch) {
            const frame = parseInt(classMatch[1]);
            const positionIndex = frameToPositionIndex[frame] ?? 0;
            const heat = getHeatForPosition(positionIndex);
            const fillOpacity = 0.15 + (heat * 0.85);

            bed.style.fillOpacity = fillOpacity;

            const paths = bed.querySelectorAll('path');
            paths.forEach(path => {
                path.style.fillOpacity = fillOpacity;
            });
        }
    });

    // Also style bull areas
    const outerBull = container.querySelector('.c-Dartboard-outerBull .c-Dartboard-bed');
    const innerBull = container.querySelector('.c-Dartboard-innerBull .c-Dartboard-bed');

    if (outerBull) {
        outerBull.style.fillOpacity = 0.6;
    }
    if (innerBull) {
        innerBull.style.fillOpacity = 0.9;
    }
}

function applyStrategicStyling(container, heatData, customOrder = null)
{
    const defaultOrder = [20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5];
    const frameOrder = customOrder || defaultOrder;

    // Create a mapping: for each frame number, find its position index in the new order
    const frameToPositionIndex = {};
    frameOrder.forEach((frame, positionIndex) => {
        frameToPositionIndex[frame] = positionIndex;
    });

    // Use position-based heat for visible changes
    const getHeatForPosition = (posIdx) => {
        const distFromTop = Math.min(posIdx, 20 - posIdx);
        return 1 - (distFromTop / 10);
    };

    // Select all bed elements and apply styling based on their frame number
    const allBeds = container.querySelectorAll('.c-Dartboard-bed');

    allBeds.forEach(bed => {
        // Extract frame number from class name
        const classMatch = bed.className.baseVal?.match(/--(\d+)/) || bed.className?.match?.(/--(\d+)/);
        if (classMatch) {
            const frame = parseInt(classMatch[1]);
            const positionIndex = frameToPositionIndex[frame] ?? 0;
            const heat = getHeatForPosition(positionIndex);

            // Apply color tint based on heat value (green to orange gradient)
            const r = Math.round(34 + (heat * (249 - 34)));
            const g = Math.round(197 - (heat * (197 - 115)));
            const b = Math.round(94 - (heat * (94 - 22)));
            bed.style.fill = `rgb(${r}, ${g}, ${b})`;
        }
    });
}

function renderThrowHeatmap()
{
    const container = document.getElementById('throwHeatmapContainer');
    const svgOverlay = document.getElementById('throwOverlaySvg');
    if (!container || !svgOverlay) return;

    const size = 340;
    const borderPercent = 0.12; // Match Dartboard class border percentage
    const innerSize = size * (1 - borderPercent); // Playing area without border
    const center = size / 2;

    // Draw the optimal aim path (green line) on SVG
    svgOverlay.setAttribute('viewBox', `0 0 ${size} ${size}`);

    // Fetch expectation data and draw the curve
    axios.get('/api/v1/dart/expectationData').then(response => {
        let data = response.data.data.data;
        data = data.sort((a, b) => a.sigma - b.sigma);

        // Filter data for the two parts of the curve
        const data1 = data.filter(d => d.sigma <= 16);
        const data2 = data.filter(d => d.sigma > 16);

        // Scale factor to convert from relative coords to pixel coords (use inner size)
        const scale = innerSize;

        // Create path for the green line
        const createPath = (points) => {
            if (points.length === 0) return '';
            const pathData = points.map((p, i) => {
                const x = (p.x * scale) + center;
                const y = center - (p.y * scale); // Flip Y
                return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
            }).join(' ');
            return pathData;
        };

        // Draw both parts of the curve
        [data1, data2].forEach(points => {
            if (points.length > 0) {
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', createPath(points));
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke', '#00ff00');
                path.setAttribute('stroke-width', '3');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('stroke-linejoin', 'round');
                svgOverlay.appendChild(path);
            }
        });

    }).catch(err => {
        console.error('Failed to load expectation data:', err);
    });
}

function applyScoreHeatmapStyling(container)
{
    // Color scale similar to Plotly's default heatmap (blue -> gray -> red)
    const colorScale = [
        { pos: 0, r: 5, g: 10, b: 172 },      // Low (blue)
        { pos: 0.35, r: 106, g: 137, b: 247 }, // Medium-low
        { pos: 0.5, r: 190, g: 190, b: 190 },  // Middle (gray)
        { pos: 0.6, r: 220, g: 170, b: 132 },  // Medium-high
        { pos: 0.7, r: 230, g: 145, b: 90 },   // High
        { pos: 1, r: 178, g: 10, b: 28 }       // Max (red)
    ];

    function interpolateColor(value) {
        // Normalize value to 0-1 (max score is 60 for T20)
        const normalized = Math.min(value / 60, 1);

        // Find the two color stops to interpolate between
        let lower = colorScale[0];
        let upper = colorScale[colorScale.length - 1];

        for (let i = 0; i < colorScale.length - 1; i++) {
            if (normalized >= colorScale[i].pos && normalized <= colorScale[i + 1].pos) {
                lower = colorScale[i];
                upper = colorScale[i + 1];
                break;
            }
        }

        // Interpolate
        const range = upper.pos - lower.pos;
        const t = range === 0 ? 0 : (normalized - lower.pos) / range;

        const r = Math.round(lower.r + t * (upper.r - lower.r));
        const g = Math.round(lower.g + t * (upper.g - lower.g));
        const b = Math.round(lower.b + t * (upper.b - lower.b));

        return `rgb(${r}, ${g}, ${b})`;
    }

    const frameOrder = [20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5];

    // Apply colors to each segment based on its score
    frameOrder.forEach((frame) => {
        // Single segments
        const singleBeds = container.querySelectorAll(`.c-Dartboard-innerSingle [class*="--${frame}"], .c-Dartboard-outerSingle [class*="--${frame}"]`);
        singleBeds.forEach(bed => {
            bed.style.fill = interpolateColor(frame);
        });

        // Double segments (2x multiplier)
        const doubleBeds = container.querySelectorAll(`.c-Dartboard-double [class*="--${frame}"]`);
        doubleBeds.forEach(bed => {
            bed.style.fill = interpolateColor(frame * 2);
        });

        // Triple segments (3x multiplier)
        const tripleBeds = container.querySelectorAll(`.c-Dartboard-triple [class*="--${frame}"]`);
        tripleBeds.forEach(bed => {
            bed.style.fill = interpolateColor(frame * 3);
        });
    });

    // Bull (25 points)
    const outerBull = container.querySelectorAll('.c-Dartboard-outerBull .c-Dartboard-bed');
    outerBull.forEach(bed => {
        bed.style.fill = interpolateColor(25);
    });

    // Double Bull (50 points)
    const innerBull = container.querySelectorAll('.c-Dartboard-innerBull .c-Dartboard-bed');
    innerBull.forEach(bed => {
        bed.style.fill = interpolateColor(50);
    });
}
