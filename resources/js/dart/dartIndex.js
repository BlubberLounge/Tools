/**
 * @author Maximilian Mewes
 *
 *
 */

import h337 from 'heatmap.js';
import * as echarts from 'echarts';

import * as UTILS from '../utils';
import Dartboard from './dartboard';
import DartCalculator from './dartCalculator';
import { count } from 'd3';

const chartOptions = {
    renderer: 'canvas',
    locale: 'DE',
};

$(function()
{
    const dartboard = new Dartboard('#dartboardContainer', {size: 380});
    dartboard.render();

    document.getElementById('gameSelection').addEventListener('change', function()
    {
        fetchData(this.value);
    });

    fetchData(document.getElementById("gameSelection").value);
});



function placeMarker(dartboardContainer, x, y, className)
{
    let hitMarker = document.createElement("i");
    hitMarker.classList.add('hitMarker', className);

    let markerWidth = window.getComputedStyle(hitMarker).width;
    let markerHeight = window.getComputedStyle(hitMarker).height;

    let leftPosition = x - (markerWidth/2); // center hitMarker on cursor tip
    let topPosition = y - (markerHeight/2); // center hitMarker on cursor tip

    hitMarker.style.setProperty("top", topPosition+"px");
    hitMarker.style.setProperty("left", leftPosition+"px");

    // console.log('hit placed');
    // console.log(`X: ${x} Y: ${y}`);

    document.getElementsByClassName('c-Dartboard')[0].append(hitMarker);
    // dartboardContainer.append(hitMarker);

    return hitMarker;
}

async function requestJson(url, cb)
{
    axios.get(url)
    .then( cb )
    .catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
            console.error('Data could not get fetched!');
        }
    });
}

/**
 *
 *
 */
async function fetchData(gameId)
{
    /**
     * Get Player skills data
     */
    // requestJson(`/api/v1/user/showDartSkills`, response => {
    //     const activity = response.data.data.activity.map( e => new Date(e.created_at));

    //     renderActivityChart(activity);
    // });
    renderSkillsChart();

    /**
     * Get Player place data
     */
    requestJson(`/api/v1/user/showPlaces`, response => {
        renderPlaceChart(response.data.data.places);
    });

    /**
     * Get Player place data
     */
    requestJson(`/api/v1/user/showPositions`, response => {
        renderPositionChart(response.data.data.positions);
    });

    /**
     * Get Player dart activity data
     */
    requestJson(`/api/v1/user/showDartActivity`, response => {
        const activity = response.data.data.activity.map( e => new Date(e.created_at));

        renderActivityChart(activity);
    });

    /**
     *
     */
    requestJson(`/api/v1/user/showGameTypes`, response => {
        renderGameTypesChart(response.data.data.gameTypes);
    });

    /**
     * Get Player throw data
     */
    requestJson(`/api/v1/dart/showThrows/${gameId}`, response => {
        const dataGame = response.data.data.game;
        const currentUser = response.data.data.user;

        _clearAll();
        renderHeatmap(dataGame, currentUser);
        // updateRadarChart('graph01', data);
        renderPlayerThrowsChart(dataGame, currentUser);
    });
}

function renderGameTypesChart(gameTypes)
{
    const ctx = document.getElementById('gameTypesGraph');
    var myChart = echarts.init(ctx, null, chartOptions);
    let data = [];

    for (const gameType of Object.keys(gameTypes)) {
        data.push({
            value: gameTypes[gameType],
            name: `${gameType}`,
        });
    }

    let option = {
        title: {
            text: 'Spieltypen %',
            left: 10,
            top: 10,
            textStyle: {
                color: '#ccc'
            }
        },
        tooltip: {
            trigger: 'item'
        },
        series: [
          {
            name: 'Spieltypen',
            type: 'pie',
            radius: ['35%', '60%'],
            avoidLabelOverlap: false,
            itemStyle: {
              borderRadius: 9,
              borderColor: 'rgba(43, 48, 53, 1)',
              borderWidth: 8
            },
            label: {
                show: true,
                color: 'rgba(255, 255, 255, 0.3)',
                formatter(param) {
                  // correct the percentage
                  return param.name + ' (' + param.percent + '%)';
                }
            },
            labelLine: {
                lineStyle: {
                  color: 'rgba(255, 255, 255, 0.3)'
                },
                smooth: 0.2,
                length: 10,
                length2: 20
            },
            data: data
          }
        ]
      };
    myChart.setOption(option);
}

function renderSkillsChart()
{
    const ctx = document.getElementById('skillsGraph');
    var myChart = echarts.init(ctx, null, chartOptions);

    let option = {
        title: {
            text: 'Spieler Skill',
            left: 'center',
            top: 10,
            textStyle: {
                color: '#ccc'
            }
        },
        radar: {
            // shape: 'circle',
            indicator: [
                { name: 'Wins', max: 100 },
                { name: 'SD', max: 1000 },
                { name: 'Avg Checkout (1D)', max: 100 },
                { name: 'Avg First 9', max: 180*3 },
                { name: 'Avg First 3', max: 180 },
                { name: 'Avg Miss', max: 100 },
                { name: 'Avg (3D)', max: 180 },
            ],
            axisName: {
                formatter(value, indicator) {
                    return `${value}\n${indicator.max}`;
                }
            },
        },
        series: [
            {
                name: 'Budget vs spending',
                type: 'radar',
                data: [{
                    value: [35, 400, 70, 300, 35, 66, 50],
                    name: 'Allocated Budget',
                    areaStyle: {
                        color: 'rgba(92, 123, 217, .6)'
                    }
                }]
            }
        ]
    };

    myChart.setOption(option);
}

function renderActivityChart(activity)
{
    function getVirtualData(year) {
        const date = +echarts.time.parse(year + '-01-01');
        const end = +echarts.time.parse(+year + 1 + '-01-01');
        const dayTime = 3600 * 24 * 1000;
        const data = [];
        for (let time = date; time < end; time += dayTime) {
          data.push([
            echarts.time.format(time, '{yyyy}-{MM}-{dd}', false),
            0
          ]);
        }
        return data;
    }

    const ctx = document.getElementById('activityGraph');
    var myChart = echarts.init(ctx, null, chartOptions);

    const currentYear = new Date().getFullYear();
    let data = [];

    for (const a of activity) {
        let currDate = echarts.time.format(a, '{yyyy}-{MM}-{dd}', false);
        let index = data.findIndex( c => c[0] == currDate );

        if(index >= 0) {
            data[index][1]++;
        } else {
            data.push([
                currDate,
                1,
            ]);
        }
    }

    const option = {
        title: {
            text: 'Aktivitäten',
            left: 'center',
            top: 10,
            textStyle: {
                color: '#ccc'
            }
        },
        tooltip: {
            position: 'top',
            formatter: function (p) {
                const format = echarts.time.format(p.data[0], '{ee} {dd}-{MM}-{yyyy}', false);
                return format + ` => Spiele ${p.data[1]}`;
            }
        },
        visualMap: {
            show: true,
            min: 2,
            max: Math.max(...data.map( e => e[1])),   // deep array max
            calculable: true,
            orient: 'horizontal',
            left: 'center',
            bottom: '5%',
            inRange: {
                color: ['#7ed3f4', '#3b4f8c'],
                opacity: [0.3, 1]
            },
            textStyle: {
                color: '#fff'
            },
        },
        calendar: {
            range: currentYear,
            top: 70,
            left: 'center',
            dayLabel: {
                color: '#fff'
            },
            monthLabel: {
                color: '#fff'
            },
            itemStyle: {
                color: '#212529',
                borderWidth: 1,
                borderColor: 'rgba(0, 0, 0, .15)',
                borderRadius: 10
            },
            // cellSize: [15, 15]
            // cellSize: [20, 20]
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        series: {
            type: 'heatmap',
            coordinateSystem: 'calendar',
            data: data,
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowColor: 'rgba(0, 0, 0, 0.25)'
                }
            },
            itemStyle: {
                color: '#000',
                borderColor: 'rgba(0, 0, 0, 0.5)'
            },
        }
    };

    myChart.setOption(option);
}

function renderPlaceChart(places)
{
    const ctx = document.getElementById('winRateGraph');
    var myChart = echarts.init(ctx, null, chartOptions);
    let data = [];

    for (const place of Object.keys(places)) {
        data.push({
            value: places[place],
            name: `Platz ${place}`,
        });
    }

    let option = {
        title: {
            text: `Plazierung %`,
            left: 10,
            top: 10,
            textStyle: {
                color: '#ccc'
            }
        },
        tooltip: {
            trigger: 'item'
        },
        series: [
          {
            name: 'Plazierung',
            type: 'pie',
            radius: ['35%', '60%'],
            avoidLabelOverlap: false,
            itemStyle: {
              borderRadius: 9,
              borderColor: 'rgba(43, 48, 53, 1)',
              borderWidth: 8
            },
            label: {
                show: true,
                color: 'rgba(255, 255, 255, 0.3)',
                formatter(param) {
                  // correct the percentage
                  return param.name + ' (' + param.percent + '%)';
                }
            },
            labelLine: {
                lineStyle: {
                  color: 'rgba(255, 255, 255, 0.3)'
                },
                smooth: 0.2,
                length: 10,
                length2: 20
            },
            data: data
          }
        ]
      };
      myChart.setOption(option);
}

function renderPositionChart(positions)
{
    const ctx = document.getElementById('positionGraph');
    var myChart = echarts.init(ctx, null, chartOptions);
    let data = [];

    for (const position of Object.keys(positions)) {
        data.push({
            value: positions[position],
            name: `Position ${position}`,
        });
    }

    let option = {
        title: {
            text: 'Positions %',
            left: 10,
            top: 10,
            textStyle: {
                color: '#ccc'
            }
        },
        tooltip: {
            trigger: 'item'
        },
        series: [
          {
            name: 'Positionierung',
            type: 'pie',
            radius: ['35%', '60%'],
            avoidLabelOverlap: false,
            itemStyle: {
              borderRadius: 9,
              borderColor: 'rgba(43, 48, 53, 1)',
              borderWidth: 8
            },
            label: {
                show: true,
                color: 'rgba(255, 255, 255, 0.3)',
                formatter(param) {
                  // correct the percentage
                  return param.name + ' (' + param.percent + '%)';
                }
            },
            labelLine: {
                lineStyle: {
                  color: 'rgba(255, 255, 255, 0.3)'
                },
                smooth: 0.2,
                length: 10,
                length2: 20
            },
            data: data
          }
        ]
      };
      myChart.setOption(option);
}

/**
 *
 */
// function updateRadarChart(id, throwData = null, user)
// {
//     let plotData = [];
//     for(let i = 0; i <= 360/20; i++)
//         plotData.push(0);

//     throwData.forEach( d => {
//         var {distance, _, degrees} =  UTILS.cartesian2Polar(d.x * -1, d.y);
//         let angle = UTILS.mod(90 - degrees, 360);

//         for(const [i, c] of plotData.entries())
//             if(i >= (angle/20)) {
//                 plotData[i-1]++;
//                 break;
//             }
//     });

//     plotData = plotData.map( d => (d / plotData.length) * 100);
//     const data = [{
//         r: plotData,
//         theta: plotData.keys(),
//         fill: 'toself',
//         type: 'scatterpolar',
//     }];

//     var layout = baseLayout;
//     layout.polar.radialaxis.range = [0, Math.max(...plotData)*1.15];

//     Plotly.newPlot(id, data, layout, baseConfig);
//     // Plotly.update(id, data);
// }

function groupByProperty(xs, key)
{
    return xs.reduce((rv, x) => {
        (rv[x[key]] = rv[x[key]] || []).push(x);
        return rv;
    }, {});
}

function renderPlayerThrowsChart(values, currentUser)
{
    const clrA = [
        '#1f77b4',  // blue, blue darker
        '#ff7f0e',  // orange, orange darker
        '#2ca02c',  // green, green darker
        '#d62728',  // red, red darker
    ];

    const clrB = [
        '#144D75',  // blue, blue darker
        '#BF5E0A',  // orange, orange darker
        '#1A611A',  // green, green darker
        '#961B1B',  // red, red darker
    ];

    const ctx = document.getElementById('expectationDataGraph');
    var myChart = echarts.init(ctx, null, {renderer: 'canvas',});

    let series = [];
    Object.values(groupByProperty(values.dart_throws, 'user_id')).forEach( (e, k) =>
    {
        let user = values.users.find( a => a.id == e[0].user_id);
        let add = user.id == currentUser.id ? '(ich)' : '';

        series.push({
            name: `${user.firstname} ${user.lastname} ${add}`,
            type: 'line',
            smooth: true,
            sampling: 'lttb',
            emphasis: {
                focus: 'series'
            },
            itemStyle: {
                color: clrA[k]
            },
            // stack: 'Total',
            data: e.map( e => e.value)
        });

        let sum = e.reduce((total, t) => total + t.value, 0);
        let avg = (sum / e.length).toFixed(2);

        series.push({
            name: `AVG ${user.firstname} ${user.lastname}`,
            type: 'line',
            showSymbol: false,
            lineStyle: {
                width: 2,
                type: 'dashed'
            },
            itemStyle: {
                color: clrB[k]
            },
            data: [...Array(e.length).fill(avg)]
        });
    });


    let option = {
        title: {
            text: 'Spieler Würfe',
            left: 'center',
            top: 10,
            textStyle: {
                color: '#ccc'
            }
        },
        toolbox: {
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        tooltip: {
          trigger: 'axis',
          position: function (pt) {
            return [pt[0], '10%'];
          }
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
        },
        yAxis: {
          type: 'value',
          boundaryGap: [0, '100%']
        },
        dataZoom: [
            {
                type: 'inside',
                start: 0,
                end: 50
            },
            {
              start: 0,
                end: 50
            }
        ],
        series: series
      };
    myChart.setOption(option);
}


/**
 *
 *
 */
function renderHeatmap(data, user)
{
    var data = data.dart_throws.filter( d => d.user_id === user.id );
    // var width = document.querySelector('#dartboardContainer').offsetWidth;
    // var height = document.querySelector('#dartboardContainer').offsetHeight;
    var width = document.getElementsByClassName('c-Dartboard')[0].offsetWidth;
    var height = document.getElementsByClassName('c-Dartboard')[0].offsetHeight;

    // if(width != height)
    //     console.warn('Something is wrong!');

    // console.log(width);
    // console.log(height);

    let widthHalf = width / 2;
    let heightHalf = height / 2;
    let multiplier = Math.min(widthHalf, heightHalf);

    let dartboardContainer = document.querySelector('#dartboardContainer');
    var points = [];

    data.forEach( w => {
        points.push({
            x: Math.round( widthHalf - (w.x * multiplier) * -1 ),
            y: Math.round( heightHalf - (w.y * multiplier) ),
            value: .5
        });
    });
    // console.table(points);

    let median = DartCalculator.calculateGeometricMedian(points, 4);
    // console.table(median);
    placeMarker(dartboardContainer, median.x, median.y, 'median');

    let centroid = DartCalculator.calculateGeometricCentroid(points);
    // console.table(centroid);
    placeMarker(dartboardContainer, centroid.x, centroid.y, 'centroid');

    let skeletonHeatmap = document.getElementById('skeleton-dartboard');
    if(skeletonHeatmap)
        skeletonHeatmap.remove();

    let heatmapInstance = h337.create({
        container: document.getElementsByClassName('c-Dartboard')[0],
        radius: 20,
    });

    let data1 = {
        max: 1,
        data: points
    };

    heatmapInstance.setData(data1);
}

function _clearAll()
{
    _clearHeatmap();
    _clearHitMarker();
}

function _clearHitMarker()
{
    let hitMarkers = document.querySelectorAll('.hitMarker');

    hitMarkers.forEach( marker => marker.remove() );
}

function _clearHeatmap()
{
    let hitMarkers = document.querySelectorAll('.heatmap-canvas');

    hitMarkers.forEach( marker => marker.remove() );
}
