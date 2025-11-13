<?php

namespace App\Enums;

/**
 * Units enumaration
 */
enum Units: string
{
    case PART = 'part';        // PARTS

    // Common volume units
    case DL = 'dl';            // Deciliter
    case CL = 'cl';            // Centiliter
    case ML = 'ml';            // Milliliter
    case OZ = 'oz';            // Fluid ounce

    // Small measure units for drinks
    case DASH = 'dash';        // Dash, a small amount of liquid
    case SPLASH = 'splash';    // Splash, typically a small splash of liquid
    case DROP = 'drop';        // Drop, very small unit of liquid

    // Solid or piece-based units
    case PIECE = 'piece';      // Piece of an ingredient
    case SLICE = 'slice';      // Slice of an ingredient
    case WEDGE = 'wedge';      // Wedge, typically used for fruit wedges
    case TWIST = 'twist';      // Twist, usually refers to a twist of citrus peel

    // Standard spoon measurements
    case TEA_SPOON = 'tsp';    // Teaspoon
    case TABLE_SPOON = 'Tbsp'; // Tablespoon
    case BAR_SPOON = 'bar spoon'; // Bar spoon, small spoon used in mixing
    case CUP = 'cup';          // Cup, commonly used in kitchen measurements
    case PINCH = 'pinch';      // Pinch, very small amount (usually for salt, spices)

    // Cocktail-specific measurements
    case JIGGER = 'jigger';    // Jigger, typically 1.5 oz (44 ml) of liquid
    case SHOT = 'shot';        // Shot, typically 1.5 oz (44 ml) of liquid
    case FINGER = 'finger';    // Finger, roughly an amount up to the height of one finger (approx. 25-50 ml)

    // Weight measurement unit
    case GRAMM = 'g';          // Gram (used for weight of ingredients)
    case SCOOP = 'scoop';

    /**
     * Get the translated name of the unit with pluralization.
     *
     * @param int $quantity
     * @return string
     */
    public function getTranslatedName(int $quantity): string
    {
        return trans_choice('units.' . $this->value, $quantity);
    }
}
