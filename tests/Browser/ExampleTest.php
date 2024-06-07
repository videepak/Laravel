<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->browse(function ($browser) {
            $browser->loginAs(\App\User::find(92))
                    ->visit('/home')
                    ->pause(5000)
                    ->clickLink("Missed Pickup Employees")
                    ->assertSee("Missed Pickup Employees List");           
                 
        });
    }
}
