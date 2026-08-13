<?php

namespace App\Http\Requests\Availability;

use App\Models\CounsellorAvailabilityBreak;

class UpdateCounsellorAvailabilityBreakRequest extends StoreCounsellorAvailabilityBreakRequest
{
    protected function availabilityBreakIdToIgnore(): ?int
    {
        $break = $this->route('availability_break')
            ?? $this->route('availabilityBreak')
            ?? $this->route('counsellorAvailabilityBreak')
            ?? $this->route('break');

        if ($break instanceof CounsellorAvailabilityBreak) {
            return $break->id;
        }

        if (is_numeric($break)) {
            return (int) $break;
        }

        return null;
    }
}
