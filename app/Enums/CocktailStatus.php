<?php

namespace App\Enums;

enum CocktailStatus: string
{
    case UNKOWN = 'unkown';
    case CREATED = 'created';
    case WAITING_FOR_REVIEW = 'waiting_for_review';
    case REVIEW = 'review';
    case REVIEWED = 'reviewed';
    case RELEASED = 'released';
    case PARTIES_ONLY = 'parties_only';
}
