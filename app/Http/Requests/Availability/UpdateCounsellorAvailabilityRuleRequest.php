<?php

namespace App\Http\Requests\Availability;

use App\Models\CounsellorAvailabilityRule;

class UpdateCounsellorAvailabilityRuleRequest extends StoreCounsellorAvailabilityRuleRequest
{
    protected function availabilityRuleIdToIgnore(): ?int
    {
        $rule = $this->route('availability_rule')
            ?? $this->route('availabilityRule')
            ?? $this->route('counsellorAvailabilityRule')
            ?? $this->route('rule');

        if ($rule instanceof CounsellorAvailabilityRule) {
            return $rule->id;
        }

        if (is_numeric($rule)) {
            return (int) $rule;
        }

        return null;
    }
}
