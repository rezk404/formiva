<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityEvent: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case StatusChanged = 'status_changed';
    case Assigned = 'assigned';
    case NoteAdded = 'note_added';
    case Converted = 'converted';
    case Declined = 'declined';
    case MarkedSpam = 'marked_spam';
    case Login = 'login';
    case LoginFailed = 'login_failed';
}
