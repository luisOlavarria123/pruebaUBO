<?php

namespace App\Enums;

enum UserRole: string
{
    case Solicitante = 'solicitante';
    case Revisor = 'revisor';
}
