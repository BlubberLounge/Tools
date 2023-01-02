
// Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
var _seed = Date.now();

export function srand(seed) {
  _seed = seed;
}

var d = document;

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
    getEl(id).innerHTML = typeof val === "number" ? val.toFixed(2) : val;   // round every number
}

export function updateIHIfDifferent(id, val)
{
    return (getEl(id).innerHTML == val ? null : setIH(id, val ?? 'invalid')) === null ? false : true;
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

export const CHART_COLORS = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
};