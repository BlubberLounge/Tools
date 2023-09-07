export const baseConfig = {
    displayModeBar: false
};

export const baseLayout = {
    title: 'no title',
    legend: {"orientation": "h"},
    xaxis: {
        // fixedrange: true,
        linecolor: 'rgba(255, 255, 255, .25)',
        gridcolor: 'rgba(255, 255, 255, .25)',
        zerolinecolor: 'rgba(255, 255, 255, .75)',
        tickfont: {
            color: 'rgba(255, 255, 255, .5)'
        },
        rangemode: 'tozero',
    },
    yaxis: {
        // fixedrange: true,
        linecolor: 'rgba(255, 255, 255, .25)',
        gridcolor: 'rgba(255, 255, 255, .25)',
        zerolinecolor: 'rgba(255, 255, 255, .75)',
        tickfont: {
            color: 'rgba(255, 255, 255, .5)'
        },
        rangemode: 'tozero',
    },
    polar: {
        radialaxis: {
            visible: true,
            // range: [0, Math.max(...plotData)*1.15],
            angle: 90,
            tickangle: 90
        },
        angularaxis: {
            direction: "clockwise",
            dtick: 360 / 20
        },
    },
    paper_bgcolor: 'rgba(0, 0, 0, 0)',
    plot_bgcolor: 'rgba(0, 0, 0, 0)',
    colorway : [
        '#1f77b4', '#144D75',   // blue, blue darker
        '#ff7f0e', '#BF5E0A',   // orange, orange darker
        '#2ca02c', '#1A611A',   // green, green darker
        '#d62728', '#961B1B',   // red, red darker
    ],
    autosize: true,
    margin: {
        t: 40,
        r: 20,
        b: 40,
        l: 20,
    }
    // DEFAULT COLORWAY
    // '#1f77b4',  // muted blue
    // '#ff7f0e',  // safety orange
    // '#2ca02c',  // cooked asparagus green
    // '#d62728',  // brick red
    // '#9467bd',  // muted purple
    // '#8c564b',  // chestnut brown
    // '#e377c2',  // raspberry yogurt pink
    // '#7f7f7f',  // middle gray
    // '#bcbd22',  // curry yellow-green
    // '#17becf'   // blue-teal
};
