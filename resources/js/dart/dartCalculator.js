
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

    static calculateStandardDeviation(throws)
    {
        var {_, standardDeviation} = this._calculateStandardDeviation(throws.map( t => t.x ));
        const standardDeviationX = standardDeviation;

        var {__, standardDeviation} = this._calculateStandardDeviation(throws.map( t => t.y ));
        const standardDeviationY = standardDeviation;

        return { standardDeviationX, standardDeviationY };
    }

    static generateRandomThrows(count, standardDeviation, mu = 0, cb)
    {
        let hits = [];

        for(let i = 0; i <= count; i++) {
            let x = this.getNormallyDistributedRandomNumber(mu, standardDeviation);
            let y = this.getNormallyDistributedRandomNumber(mu, standardDeviation);
            let xx = cb(x);
            let yy = cb(y);
            let points = this.getScoreCartesian(xx, yy);
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

    static calculateGeometricCentroid(points)
    {
        let xSum = 0;
        let ySum = 0;

        points.forEach( point =>
        {
            xSum += point.x;
            ySum += point.y;
        });

        return {x: xSum / points.length, y: ySum / points.length};
    }

    static calculateGeometricMedian(arr, n)
    {
        // Current x coordinate and y coordinate
        let current_point = {x:0, y:0};

        let test_point = [{x:-1, y:0}, {x:0, y:1}, {x:1, y:0}, {x:0, y:-1}];
        let lower_limit = 0.01;

        for (let i = 0; i < n; i++) {
            current_point.x = current_point.x + arr[i].x;
            current_point.y = current_point.y + arr[i].y;
        }

        // Here current_point becomes the
        // Geographic MidPoint
        // Or Center of Gravity of equal
        // discrete mass distributions
        current_point.x /= n;
        current_point.y /= n;

        // minimum_distance becomes sum of
        // all distances from MidPoint to
        // all given points
        let minimum_distance = this._distSum(current_point, arr, n);

        let k = 0;
        while (k < n) {
            for (let i = 0; i < n, i != k; i++) {
                let newpoint = {x:0, y:0};
                newpoint.x = arr[i].x;
                newpoint.y = arr[i].y;
                let newd = this._distSum(newpoint, arr, n);
                if (newd < minimum_distance) {
                    minimum_distance = newd;
                    current_point.x = newpoint.x;
                    current_point.y = newpoint.y;
                }
            }
            k++;
        }

        // Assume test_distance to be 1000
        let test_distance = 1000;
        let flag = 0;

        // Test loop for approximation starts here
        while (test_distance > lower_limit) {

            flag = 0;

            // Loop for iterating over all 4 neighbours
            for (let i = 0; i < 4; i++) {

                // Finding Neighbours done
                let newpoint = {};
                newpoint.x = current_point.x + test_distance * test_point[i].x;
                newpoint.y = current_point.y + test_distance * test_point[i].y;

                // New sum of Euclidean distances
                // from the neighbor to the given
                // data points
                let newd = this._distSum(newpoint, arr, n);

                if (newd < minimum_distance) {

                    // Approximating and changing
                    // current_point
                    minimum_distance = newd;
                    current_point.x = newpoint.x;
                    current_point.y = newpoint.y;
                    flag = 1;
                    break;
                }
            }

            // This means none of the 4 neighbours
            // has the new minimum distance, hence
            // we divide by 2 and reiterate while
            // loop for better approximation
            if (flag == 0)
                test_distance /= 2;
        }

        // console.log("Geometric Median = (", current_point.x, ", ", current_point.y, ") with minimum distance = ", minimum_distance.toFixed(5));
        return { x:current_point.x, y:current_point.y };
    }

    /** = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
     *
     *      helper / private methods
     *
     * = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    /**
     *
     */
    static _createMatrix(size, value = 1)
    {
        let result = [];
        for(let i = -size; i < size+1; i++) {
            let rows = [];
            for(let j = -size; j < size+1; j++)
                rows.push(value);

            result.push(rows);
        }

        return result;
    }

    /**
     *
     *
     */
    static _calculateStandardDeviation(data)
    {
        let mean = data.reduce( (total, current) => total + current, 0 ) / data.length;

        data = data.map( k => (k - mean) ** 2) ;
        let sum = data.reduce( (total, current) => total + current, 0 );

        const variance = sum / data.length;
        const standardDeviation = Math.sqrt(sum / data.length);

        return { variance, standardDeviation };
    }

    /**
     *
     *
     */
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

    /**
     *
     *
     */
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

    /**
     *
     *
     */
    static _distSum(p, arr, n)
    {
        let sum = 0;
        for (let i = 0; i < n; i++) {
            let distX = Math.abs(arr[i].x - p.x);
            let distY = Math.abs(arr[i].y - p.y);
            sum += Math.sqrt((distX * distX) + (distY * distY));
        }
        return sum;
    }
}
