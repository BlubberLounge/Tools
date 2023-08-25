
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

        return this.getScorePolar(r, theta);
    }

    static getScorePolar(radius, theta)
    {
        if (radius <= DartDefinition.ringRadiiAbsolute.doubleBullseye.end)
            return 50;

        if(radius <= DartDefinition.ringRadiiAbsolute.bullseye.end)
            return 25;

        let ringIndex = this._getRingIndex(radius);

        theta = 90 - theta - DartDefinition.fieldSizeDeg / 2;
        theta = UTILS.mod(theta, 360);
        let field = this.getField(theta);

        return DartDefinition.fieldMultiplier[ringIndex] * field;
    }

    static _getRingIndex(radius)
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

    static calculateStandardDeviation(reference, throws)
    {
        let throwsDistanceMean = this._calculateThrowDistances(reference, throws);
        let {variance, standardDeviation} = this._calculateStandardDeviation(throwsDistanceMean);

        return { variance, standardDeviation };
    }

    static _calculateStandardDeviation(data)
    {
        let mean = data.reduce( (total, current) => total + current, 0 ) / data.length;

        data = data.map( k => (k - mean) ** 2) ;
        let sum = data.reduce( (total, current) => total + current, 0 );

        const variance = sum / data.length;
        const standardDeviation = Math.sqrt(sum / data.length);

        return { variance, standardDeviation };
      }

    static _calculateThrowDistances(origin, throws)
    {
        let distances = [];
        throws.forEach( t =>
        {
            let distX = Math.abs(origin.x - t.x);
            let distY = Math.abs(origin.y - t.y);
            let distXSq = distX * distX;
            let distYSq = distY * distY;

            distances.push(Math.sqrt( distXSq + distYSq ));
        });

        return distances;
    }

    static generateRandomThrows(count, standardDeviationm, mu = 0)
    {
        let hits = [];

        for(let i = 0; i <= count; i++) {
            let x = this.getNormallyDistributedRandomNumber(mu, standardDeviationm);
            let y = this.getNormallyDistributedRandomNumber(mu, standardDeviationm);
            let points = this.getScoreCartesian(x, y);
            hits.push({
                x: x,
                y: y,
                points: points,
            });
        }

        return hits;
    }

    static getNormallyDistributedRandomNumber(mu, sigma)
    {
        const { z0, _ } = this._boxMullerTransform();

        return sigma * z0 + mu;
    }

    static _boxMullerTransform()
    {
        // Convert [0,1) to (0,1), exclude 0
        const u1 = 1 - Math.random();
        const u2 = 1 - Math.random();
        const theta = 2.0 * Math.PI * u2;
        const mag = Math.sqrt(-2.0 * Math.log(u1));

        const z0 = mag * Math.cos(theta);
        const z1 = mag * Math.sin(theta);

        return { z0, z1 };
    }
}
