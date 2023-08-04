class Hit
{
    constructor(x, y, points, fieldName, ringName)
    {
        this.x = x;
        this.y = y;
        this.points = points;
        this.fieldName = fieldName;
        this.ringName = ringName;
    }
}

export {
    Hit as default,
}
