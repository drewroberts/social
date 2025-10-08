<?php

describe('Homepage', function () {
    it('returns successful response', function () {
        $this->get('/')->assertOk();
    });
});
