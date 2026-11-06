<?php


test('the root url sends visitors to the student sign-in page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/start-exam');
});
