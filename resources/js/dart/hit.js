class Hit
{
    constructor(x, y, points, field, fieldName, ring, ringName)
    {
        this.x = x;
        this.y = y;
        this.points = points;
        this.field = field;
        this.fieldName = fieldName;
        this.ring = ring;
        this.ringName = ringName;
    }
}

export {
    Hit as default,
}
