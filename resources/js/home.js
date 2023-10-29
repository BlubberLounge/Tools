
require('./dart-queue');

document.getElementById('BtnShare').addEventListener('click', event => {
    if (navigator.share) {
        navigator.share({
            title: 'BlubberLounge Tools',
            url: window.location.origin
        }).then(() => {
            console.log('Thanks for sharing!');
        }) .catch(console.error);
    }
});

/**
 * Get Player throw data
 */
requestJson(`/api/v1/dart/showThrows/9a42af70-31d9-4152-a851-73cb15f9ca58/user/1`, response => {


    renderMicroCharts(dataGame, currentUser);
});

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

function renderMicroCharts(gameTypes)
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
