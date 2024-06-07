<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLogin()
    {

		$response = $this->json(
			'POST',
			'http://127.0.0.1:8000/api/v7/login',
			[
				"email" => "info00@communitystepup.org",
				"password" => "123456",
				"platform" => "IOS",
				"device_token" => "das",
				"appVersion" => "1.2.3"
			]
		)
		->assertStatus(200);        
    }

    public function testPropertyList()
    {

		$this->json(
				'POST',
				'http://127.0.0.1:8000/api/v7/propertiesListV2',
				[],
				['Authorization' => 'Bearer 7770dd34c013568b988da3af059f21c2']
			)
			->assertStatus(200)
			->assertJson(
				[
	            	'created' => true,
	        	]
    	);
    }
}
