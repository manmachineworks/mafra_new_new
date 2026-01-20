<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class EmailOtpTest extends TestCase
{
    // usage of RefreshDatabase might wipe the DB, let's avoid it or use it carefully.
    // typically in existing projects we might not want to wipe existing data if not using a separate testing DB.
    // For safety, I will create a test user and delete it after, or use transactions if configured.
    // Seeing 'RefreshDatabase' is common practice but dangerous if .env.testing isn't set up.
    // I'll skip RefreshDatabase trait and manage the user manually to be safe on this existing environment.

    public function test_can_send_otp_to_email()
    {
        $user = User::where('email', 'feature_test_user@example.com')->first();
        if (!$user) {
            $user = new User();
            $user->name = 'Test User';
            $user->email = 'feature_test_user@example.com';
            $user->password = bcrypt('password');
            $user->save();
        }

        $response = $this->post(route('send-otp'), [
            'phone' => $user->email // The controller expects 'phone' field to contain email
        ]);

        // Debug output if fails
        if ($response->status() !== 200) {
            dump($response->getContent());
        }

        $response->assertStatus(200);
        $response->assertJson(['result' => true]);

        // Verify OTP was saved
        $user->refresh();
        $this->assertNotNull($user->otp_code);
    }

    public function test_can_login_with_email_otp()
    {
        $user = User::where('email', 'feature_test_user@example.com')->first();
        if (!$user) {
            $this->fail('User not found for login test. Run send_otp test first.');
        }

        // Ensure OTP is set (might have been cleared or expired)
        $user->otp_code = '123456';
        $user->otp_sent_time = date("Y-m-d H:i:s");
        $user->save();

        $response = $this->post(route('validate-otp-code'), [
            'phone' => $user->email,
            'otp_code' => '123456'
        ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']); // Ajax request

        // Debug output if fails
        if ($response->status() !== 200) {
            dump($response->getContent());
        }

        $response->assertStatus(200);
        $response->assertJson(['result' => true]);

        $this->assertAuthenticatedAs($user);

        // Cleanup
        $user->delete();
    }
}
