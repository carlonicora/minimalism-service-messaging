<?php

namespace CarloNicora\Minimalism\Services\Messaging\Databases\Messaging\Tables\Enums;

enum ParticipantStatus: int
{
    case Active = 0;
    case Archived = 1;
}