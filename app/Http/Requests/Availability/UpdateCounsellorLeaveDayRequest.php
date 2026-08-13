<?php

namespace App\Http\Requests\Availability;

use App\Models\CounsellorLeaveDay;

class UpdateCounsellorLeaveDayRequest extends StoreCounsellorLeaveDayRequest
{
    protected function leaveDayIdToIgnore(): ?int
    {
        $leaveDay = $this->route('leave_day')
            ?? $this->route('leaveDay')
            ?? $this->route('counsellorLeaveDay');

        if ($leaveDay instanceof CounsellorLeaveDay) {
            return $leaveDay->id;
        }

        if (is_numeric($leaveDay)) {
            return (int) $leaveDay;
        }

        return null;
    }
}
