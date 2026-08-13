<?php

namespace App\Http\Requests\Availability;

use App\Models\CounsellorBlockedSlot;

class UpdateCounsellorBlockedSlotRequest extends StoreCounsellorBlockedSlotRequest
{
    protected function blockedSlotIdToIgnore(): ?int
    {
        $blockedSlot = $this->route('blocked_slot')
            ?? $this->route('blockedSlot')
            ?? $this->route('counsellorBlockedSlot');

        if ($blockedSlot instanceof CounsellorBlockedSlot) {
            return $blockedSlot->id;
        }

        if (is_numeric($blockedSlot)) {
            return (int) $blockedSlot;
        }

        return null;
    }
}
