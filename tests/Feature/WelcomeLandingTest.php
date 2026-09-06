<?php

it('renders the genealogy landing at /', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Keep the people and stories together.', false);
    $response->assertSee('Begin your family history', false);
    $response->assertSee('Liberu Genealogy', false);
    $response->assertSee('https://github.com/liberusoftware/genealogy-laravel', false);
});

it('links the landing CTAs to the real auth routes', function () {
    $this->get('/')
        ->assertSee(route('register'), false)
        ->assertSee(route('login'), false);
});

it('no longer ships the stock Laravel welcome scaffold', function () {
    $this->get('/')->assertDontSee("Let's get started", false);
});
