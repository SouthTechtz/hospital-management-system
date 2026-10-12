<?php

namespace App\Enums;

enum AuditAction: string
{
    case Login = 'login';
    case LoginFailed = 'login_failed';
    case Logout = 'logout';
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Export = 'export';
    case Print = 'print';
    case BreakGlass = 'break_glass';
    case PermissionChange = 'permission_change';
    case KeyOperation = 'key_operation';
    case Void = 'void';
    case Merge = 'merge';
    case Unmerge = 'unmerge';
    case MfaEnabled = 'mfa_enabled';
    case MfaDisabled = 'mfa_disabled';

    public function labelKey(): string
    {
        return 'enums.audit_action.'.$this->value;
    }
}
