<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2022 Alkari Verende
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Kassie\Calendar\Http\Validation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class Settings.
 */
class SettingsValidation extends FormRequest
{
    /**
     * Authorize the request by default.
     */
    public function authorize(): bool
    {

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'slack_emoji_importance_full' => ['nullable', 'string'],
            'slack_emoji_importance_half' => ['nullable', 'string'],
            'slack_emoji_importance_empty' => ['nullable', 'string'],
            'notify_operation_interval' => ['nullable', 'string'],
            'notify_locale' => ['string'],
            'notify_create_operation' => ['boolean'],
            'notify_update_operation' => ['boolean'],
            'notify_cancel_operation' => ['boolean'],
            'notify_activate_operation' => ['boolean'],
            'notify_end_operation' => ['boolean'],
            'discord_integration' => ['boolean'],
            'discord_client_id' => ['nullable', 'string'],
            'discord_client_secret' => ['nullable', 'string'],
            'discord_bot_token' => ['nullable', 'string'],
            'discord_allowed_channels' => ['nullable', 'array'],
        ];
    }
}
