<?php
namespace App\Enum;

enum EventState: string {
case CREATED = 'créée';
case OPENED = 'ouverte';
case CLOSED = 'cloturée';
case PROCESSING = 'cours';
case FINISHED = 'passée';
case CANCELLED = 'annulée';
}
