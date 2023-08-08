<?php

uses(Tests\TestCase::class)->in('Feature', 'Unit');

expect()->extend('toOutput', function (string $output) {
    $value = $this->value;

    if (windows_os()) {
        $value = str_replace("\r\n", "\n", $value);
    }

    return $this->and($value)->toBe($output);
});
