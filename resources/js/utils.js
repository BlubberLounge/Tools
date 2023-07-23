import { DateTime } from "luxon";

// Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
var _seed = Date.now();

export function srand(seed) {
  _seed = seed;
}

var d = document;

export function roundIfNumber(val)
{
    return typeof val === "number" ? val.toFixed(2) : val
}

export function getEl(id)
{
    return d.getElementById(id);
}

export function getVal(id)
{
    return getEl(id).value;
}

export function setVal(id, val)
{
    getEl(id).value = val;
}

export function setIH(id, val)
{
    getEl(id).innerHTML = roundIfNumber(val);   // round every number
}

export function updateIHIfDifferent(id, val)
{
    // console.log([id, (getEl(id).innerHTML == roundIfNumber(val))]);
    return (getEl(id).innerHTML == roundIfNumber(val) ? null : setIH(id, val ?? 'invalid')) === null ? false : true;
}

export function uInfo(id, val, unit = undefined)
{
    return updateIHIfDifferent(id, val) || (typeof unit !== "undefined" ? updateIHIfDifferent(id+"Unit", unit) : false);
}

export function Z(n)
{    // adds a leading zero
    return n < 10 ? "0" + n : n;
};

export function timeNow()
{
    let d = new Date(); // new Date().toLocaleString();
    let hh = Z(d.getHours());
    let mm = Z(d.getMinutes());
    let ss = Z(d.getSeconds());
    return [hh, mm, ss].join(':');
}

export function dateNow()
{
    let d = new Date(); // new Date().toLocaleString();
    let dd = Z(d.getDate());
    let mm = Z(d.getMonth()+1);
    let yyyy = Z(d.getFullYear());
    return "<span class='small text-muted'>"+ [dd, mm, yyyy].join('.') + "</span>";;
}

export function now()
{
    return Date.now();
}

export function msToString(ms)
{
    let d = new Date(ms);
    let hh = Z(d.getHours());
    let mm = Z(d.getMinutes());
    let ss = Z(d.getSeconds());
    return [hh, mm, ss].join(':');
}

export function currentTime()
{
    let timeString = dateNow() +" - "+ timeNow();
    setIH('currentTime', timeString);
    setTimeout(currentTime, 1000);
}

export function onClick(id, callback)
{
    getEl(id).addEventListener('click', callback);
}

export function onChange(id, callback)
{
    getEl(id).addEventListener('change', callback);
}

export function disableInputs()
{
    // Object.keys(settings['Default 18650']).forEach(o =>
    // {
    //     getEl(o).disabled = true;
    // });
    getEl('fieldsetParameter').disabled = true;
}

export function enableInputs()
{
    // Object.keys(settings['Default 18650']).forEach(o =>
    // {
    //     getEl(o).disabled = false;
    // });
    getEl('fieldsetParameter').disabled = false;
}

export function applyPreset(l, opt)
{
    Object.entries(l[opt]).forEach(o =>
    {
       setVal(o[0], o[1]);
    });
}

export function chartGradient(id)
{
    var ctx = getEl('dischargeCurveChart').getContext('2d');

    const g = ctx.createLinearGradient(0, 0, 0, 350)
    g.addColorStop(0, 'rgba(58,123,213,1)');
    g.addColorStop(1, 'rgba(0,210,255,.275)');

    return g
}

export function toggleChartAnnotaion(switchId, AnnId, chart)
{
    onChange(switchId, function(e)
    {
        let b = getEl(switchId).checked === true;
        let a = chart.options.plugins.annotation.annotations;
        if(AnnId == 1) {
            a.A50.display = b;
            a.SOCrpower.display = b;
            a.SOCpower.display = b;
        } else if(AnnId == 2) {
            a.COlow.display = b;
            a.COhigh.display = b;
        }
        chart.update();
    });
}

export function displayAnnotation(c, o)
{
    let id = c.id;
    if(id === "COlow" || id === "COhigh")
        return getEl("switchCutOffs").checked === true;
    else if(id === "A50" || id === "SOCrpower" || id === "SOCpower")
        return getEl("switch50Marks").checked === true;

    return true;
}

// https://web.archive.org/web/20090717035140if_/javascript.about.com/od/problemsolving/a/modulobug.htm
export function mod(n, m)
{
    return ((n % m) + m) % m;
}


export function integral(f, s, e, acc = .01)
{
    let area = 0;
    do
    {
        area += Math.abs ( f(s, 0, 100) ) * acc;
        //func finds the height of the rect & delta is the width
        // we use abs because a negative area doesn't make sense
        s += acc; //move forward by the width of the rect
    }
    while ( s <= e ) ; //go until we reach the end

    return area.toFixed(5);
}

export function cartesian2Polar(x, y)
{
    let distance = Math.sqrt(x*x + y*y);
    let radians = Math.atan2(y,x);

    return { distance:distance, radians:radians, degrees: radians * (180/Math.PI) };
}

export function asPercent(number)
{
    return (number / 100).toFixed(2)
}

class BaseData
{
    data = {};
    constructor()
    {

    }

    exec()
    {
        console.log("not implemented.");
    }
}

export class ExponentialSmoothing extends BaseData
{
    constructor()
    {
        super();
    }

    exec()
    {

    }
}

export class MovingAverage extends BaseData
{
    constructor()
    {
        super();
    }

    exec()
    {

    }
}


/* =============================================================================
 * |
 * |            COLOR utils
 * |
 * =============================================================================
 */


export const CHART_COLORS = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
};

export class CRGB
{
    constructor(r, g, b, a = 1)
    {
        this.instance = this;

        this.setRGBA(r, g, b, a);
        return this;
    }

    normalize()
    {
        // make sure that value are in 0 ... 1 format
        // instead of 0 .. 255
        /*
        this.r = Number.isInteger(r) && r > 1 ?  r / 256 : r;
        this.g = Number.isInteger(g) && g > 1 ?  g / 256 : g;
        this.b = Number.isInteger(b) && b > 1 ?  b / 256 : b;
        */

        this.r /= 255;
        this.g /= 255;
        this.b /= 255;

        return this;
    }

    to8Bit()
    {
        let r = this.r * 255;
        let g = this.g * 255;
        let b = this.b * 255;
        let a = this.a * 255;
        return [r, g, b, a];
    }

    setRGB(r, g, b)
    {
        this.setRGBA(r, g, b, 1);
        return this;
    }

    setRGBA(r, g, b, a)
    {
        this.r = Number(r);
        this.g = Number(g);
        this.b = Number(b);
        this.a = Number(a);

        this.normalize();

        return this;
    }

    toString()
    {
        return 'rgba('+this.to8Bit().join(', ')+')';
    }

    gammaAdjust()
    {
        const LOW_GAMMA_THRESHOLD = 0.03928;
        const LOW_GAMMA_ADJUSTMENT_COEFFICIENT = 12.92;
        let r = this.r <= LOW_GAMMA_THRESHOLD ? this.r / LOW_GAMMA_ADJUSTMENT_COEFFICIENT : this.highGammaAdjust(this.r);
        let g = this.g <= LOW_GAMMA_THRESHOLD ? this.g / LOW_GAMMA_ADJUSTMENT_COEFFICIENT : this.highGammaAdjust(this.g);
        let b = this.b <= LOW_GAMMA_THRESHOLD ? this.b / LOW_GAMMA_ADJUSTMENT_COEFFICIENT : this.highGammaAdjust(this.b);

        return {r: r, g: g, b: b};
    }

    highGammaAdjust(val)
    {
        return Math.pow((val + 0.055) / 1.055, 2.4);
    }

    relativeLuminance()
    {
        let adjustedColor = this.gammaAdjust();
        return 0.2126 * adjustedColor.r + 0.7152 * adjustedColor.g + 0.0722 * adjustedColor.b;
    }

    contrastRatio(CRGB)
    {
        let clr1 = this.relativeLuminance();
        let clr2 = CRGB.relativeLuminance();
        let l1 =  Math.max(clr1, clr2);
        let l2 =  Math.min(clr1, clr2);
        return (l1 + 0.05) / (l2 + 0.05);
    }

    color = {
        red: 'rgb(255, 99, 132)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(75, 192, 192)',
        blue: 'rgb(54, 162, 235)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(201, 203, 207)',
        // Bootstrap colors
        primary: '#0d6efd',
        secondary: '#6c757d',
        success: '#198754',
        info: '#0dcaf0',
        warning: '#ffc107',
        danger: '#dc3545',
        light: '#f8f9fa',
        dark: '#212529',
    };

    // get r()
    // {
    //     return this.r * 255;
    // }

    // get g()
    // {
    //     return this.g * 255;
    // }

    // get b()
    // {
    //     return this.g * 255;
    // }

    // get a()
    // {
    //     return this.a;
    // }
};
