<?php

namespace App\Enums;

enum NotificationType: string
{
    case PanneSignalee = 'panne_signalee';
    case PanneAssignee = 'panne_assignee';
    case InterventionDemarree = 'intervention_demarree';
    case InterventionCloturee = 'intervention_cloturee';
    case DeadlineProche = 'deadline_proche';
    case DeadlineDepassee = 'deadline_depassee';
}
