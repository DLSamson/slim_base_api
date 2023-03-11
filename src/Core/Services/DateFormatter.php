<?php

namespace Api\Core\Services;

class DateFormatter {
    public static function formatToISO8601($date) {
        return date("c", strtotime($date));
    }
}