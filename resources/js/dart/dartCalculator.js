
import * as UTILS from '../utils';
import DartDefinition from './dartDefinition';

/**
 *
 *
 *
 */
export default class DartCalculator
{
    constructor()
    {
        // create singleton at a later point for performance optimization
        // if(DartCalculator.instance)
        //     return DartCalculator.instance;

        // DartCalculator.instance = this;
    }

    static getScoreCartesian(x, y)
    {
        // if(!normalized) {
        //     let {x, y} = this.normalizeCartesian(x, y);
        // }

        let r = Math.sqrt(x*x + y*y);

        if (r <= DartDefinition.ringRadiiAbsolute.doubleBullseye.end)
            return 50;

        if(r <= DartDefinition.ringRadiiAbsolute.bullseye.end)
            return 25;

        const radsToDegs = rad => rad * 180 / Math.PI;
        let theta = 0;
        theta = radsToDegs(Math.atan2(y, x));
        console.log(r);
        return this.getScorePolar(r, theta);
    }

    static getScorePolar(radius, theta)
    {
        if (radius <= DartDefinition.ringRadiiAbsolute.doubleBullseye.end)
            return 50;

        if(radius <= DartDefinition.ringRadiiAbsolute.bullseye.end)
            return 25;

        let ringIndex = this.getRingIndex(radius);

        theta = theta - DartDefinition.fieldSizeDeg / 2;
        theta = UTILS.mod(theta + 360, 360);
        let field = this.getField(theta);

        return DartDefinition.fieldMultiplier[ringIndex] * field;
    }

    static getRingIndex(radius)
    {
        return DartDefinition.getRingRadiiAbsoluteArray().findIndex( ring => ring.end >= radius );
    }

    static getField(angle)
    {
        let tmp = [];
        for(let i = 0; i < DartDefinition.fieldOrder1.length; i++)
            tmp.push(DartDefinition.fieldSizeDeg * i);

        let ind = tmp.findIndex( f => f >= angle ) > 0 ? tmp.findIndex( f => f >= angle ) : DartDefinition.fieldOrder1[DartDefinition.fieldOrder1.length-1] ;

        return DartDefinition.fieldOrder1[ind-1];
    }

    static normalizeCartesian(x, y)
    {
        return {
            x: this.normalize(x),
            y: this.normalize(y)
        };
    }

    static normalizePolar(radius, theta)
    {
        return {
            radius: this.normalize(radius),
            theta: theta
        };
    }

    static normalize(value)
    {
        return value / DartDefinition.dartboardRadius;
    }
}
