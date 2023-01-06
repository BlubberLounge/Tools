import * as UTILS from '../utils';

/**
 * represents a single battery cell
 * 
 */
export default class Battery
{
    // https://www.batterypowertips.com/how-to-read-battery-discharge-curves-faq/
    // https://electronics.stackexchange.com/questions/107049/working-out-mah-from-current-and-time
    // https://www.youtube.com/watch?v=rOwcxFErcvQ SOC Stafe of charge SOC = Remaining capacity (Ah) / total capacity (Ah)
    // Average consumption = (Consumption1 × Time1 + Consumption2 × Time2) / (Time1 + Time2)
    
    // To calculate the battery size for a varying load which requires I1 in the interval t1 and I2 in the remaining time:
    // Estimate the average load current — Iav = (I1 × t1 / t) + (I2 × [t - t1 / t]).
    // Substitute I = Iav in the equation for battery capacity of lithium-ion. B = 100 × I × t / (100 - q) where B is the battery capacity, I is the load current, t is the duration of power supply, and q is the percentage of charge which should remain in the battery after the discharge.
    
    
    // initial parameter setting presets
    availablePresets = {
        'Default 18650':
        {
            'batteryMaxVoltage': 4.2,
            'batteryMinVoltage': 2.7,
            'batteryCapacity': 3500,
            'batteryLevel': 100,
            'readInterval': 5000,
            'staticLoad': 450,
        },
        'High load 18650':
        {
            'batteryMaxVoltage': 4.2,
            'batteryMinVoltage': 2.7,
            'batteryCapacity': 3500,
            'batteryLevel': 100,
            'readInterval': 10000,
            'staticLoad': 350000,
        },
        'low Capacity 18650':
        {
            'batteryMaxVoltage': 4.2,
            'batteryMinVoltage': 2.6,
            'batteryCapacity': 2600,
            'batteryLevel': 100,
            'readInterval': 10000,
            'staticLoad': 450,
        },
        '1/2 full 18650':
        {
            'batteryMaxVoltage': 4.2,
            'batteryMinVoltage': 2.7,
            'batteryCapacity': 3500,
            'batteryLevel': 50,
            'readInterval': 5000,
            'staticLoad': 450,
        },
        '2S3P Battery (18650)':
        {
            'batteryMaxVoltage': 3.6*2,
            'batteryMinVoltage': 2.6,
            'batteryCapacity': 2500*3,
            'batteryLevel': 100,
            'readInterval': 5000,
            'staticLoad': 450,
        },
    };

    underLoad = false;
    underSameLoadSince = 0;
    loadBuffer = [];
    log = [];

    // not used yet
    type = 'unkown';
    cRate = undefined;
    temperature = undefined;
    internalResistance = undefined;
    stateOfHealth = undefined;  // SOH
    DepthOfCharge = undefined; // DOP

    constructor(maxVoltage, minVoltage, capacity, level)
    {
        this.voltage = parseFloat(maxVoltage);
        this.capacity = Number(capacity);                           // calculated
        this.level = Number(level);                                 // calculated, SOC = State Of Charge
        this.power = Number((maxVoltage-minVoltage)*this.capacity);   // nominal voltage * capacity

        this.initlevel = this.level;                                // max. / inittial
        this.initCapacity = this.capacity;                          // max. / inittial, Nominal capacity
        this.maxVoltage = parseFloat(maxVoltage);
        this.minVoltage = parseFloat(minVoltage);
    }

    calculateStats()
    {
        if(this.loadBuffer.length < 1)
            return;

        // add current buffer content to the log/history array
        this.loadBuffer.forEach((e, i) => {
            if(!e.timer)    // ignore running periods / last period in the best case
                return;

            // clear buffer
            this.loadBuffer.splice(i, 1);

            this.log.push(e);

            this.capacity -= e.dischargeCurrent * (e.timer / 60 / 60 / 1000);
            // this.level = this.linearMap(this.voltage, this.minVoltage, this.maxVoltage); 
            this.level = this.linearMap(this.capacity, 0, this.initCapacity); 
        });
    }

    currentLoad(load)
    {
        if(load < 1) {
            this.underLoad = false;
            return;
        }
        
        this.addToBuffer(load);
        this.underLoad = true;
    }

    addToBuffer(dischargeCurrent)
    {
        if(this.loadBuffer.length >= 1)
            // trying to reduce buffer entries, especially when having static loads
            if(dischargeCurrent == this.loadBuffer[this.loadBuffer.length-1].dischargeCurrent) {
                // if the battery is under the same load for 3 sec go ahead
                if(UTILS.now() - this.underSameLoadSince < 3000) {
                    return; 
                } else {
                    this.underSameLoadSince = UTILS.now();
                }
            }
        
        // create buffer entry
        let l = {};
        l.startTime = UTILS.now();
        l.endTime = undefined;
        l.timer = 0; // track load time for later calculation
        l.dischargeCurrent = dischargeCurrent;

        // fill last entries endTime and calculate the startTime and endTime delta = timer
        if(this.loadBuffer.length >= 1) {
            this.loadBuffer[this.loadBuffer.length-1].endTime = l.startTime+1; // in case some other js thing gets in between use last startTime + 1 be be more accurate
            this.loadBuffer[this.loadBuffer.length-1].timer = this.loadBuffer[this.loadBuffer.length-1].endTime - this.loadBuffer[this.loadBuffer.length-1].startTime;
        }

        this.loadBuffer.push(l);
    }

    calculateCRate(current)
    {
        return this.cRate = this.capacity / current;
    }

    calculateDischargeCurrent()
    {
        return this.cRate * this.capacity;
    }

    calculateRunTime()
    {
        return this.runTime = 1 / this.cRate;
    }

    calculateSOCPower()
    {
        return this.linearMap(this.power, 0, this.power);
    }

    linearMap(v, min, max)
    {
        // let p = (v-min)*100/ (max-min); 
        let p = (v - min) * (100 - 0) / (max - min) + 0;
        return p >= 100 ? 100 : p <= 0 ? 0 : p;
    }

    /**
     * Symmetric sigmoidal approximation
     * https://www.desmos.com/calculator/7m9lu26vpy
     *
     * c - c / (1 + k*x/v)^3
     */
    sigmoidal(v, min, max)
    {
	// slow
	// uint8_t result = 110 - (110 / (1 + pow(1.468 * (voltage - minVoltage)/(maxVoltage - minVoltage), 6)));

	// steep
	// uint8_t result = 102 - (102 / (1 + pow(1.621 * (voltage - minVoltage)/(maxVoltage - minVoltage), 8.1)));

	// normal
        let result = 105 - (105 / (1 + Math.pow(1.724 * (v - min)/(max - min), 5.5)));
        return result >= 100 ? 100 : result;
    }

    /**
     * Asymmetric sigmoidal approximation
     * https://www.desmos.com/calculator/oyhpsu8jnw
     *
     * c - c / [1 + (k*x/v)^4.5]^3
     */
    asigmoidal(v, min, max)
    {
        let result = 101 - (101 / Math.pow(1 + Math.pow(1.33 * (v - min)/(max - min) ,4.5), 3));
        return result >= 100 ? 100 : result;
    }
}