<?php

namespace CarloNicora\Minimalism\Services\Messaging\Data\Databases\Messaging\Tables\Enums;

enum ParticipantStatus: int
{
    case ACTIVE = 0;
    case ARCHIVED = 1;
}