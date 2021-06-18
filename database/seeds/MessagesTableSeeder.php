<?php

use Illuminate\Database\Seeder;

class MessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('messages')->insert([
            [
                'sender_id' => 1,
                'recipient_id' => 2,
                'reply_to_id' => 0,
                'subject' => 'Message Subject',
                'content' => 'Lorem ipsum',
                'read' => 0,
                'created_at' => '2017-12-01 12:00:00',
                'updated_at' => '2017-12-01 12:00:00'
            ],
            [
                'sender_id' => 1,
                'recipient_id' => 2,
                'reply_to_id' => 0,
                'subject' => 'Message Subject',
                'content' => 'Lorem ipsum',
                'read' => 1,
                'created_at' => '2017-12-02 12:00:00',
                'updated_at' => '2017-12-02 12:00:00'
            ],
            [
                'sender_id' => 1,
                'recipient_id' => 2,
                'reply_to_id' => 0,
                'subject' => 'Message Subject',
                'content' => 'Lorem ipsum',
                'read' => 0,
                'created_at' => '2017-12-01 12:00:00',
                'updated_at' => '2017-12-01 12:00:00'
            ],
            [
                'sender_id' => 1,
                'recipient_id' => 3,
                'reply_to_id' => 0,
                'subject' => 'Message Subject',
                'content' => 'Lorem ipsum',
                'read' => 0,
                'created_at' => '2017-12-01 11:00:00',
                'updated_at' => '2017-12-01 11:00:00'
            ],
            [
                'sender_id' => 1,
                'recipient_id' => 4,
                'reply_to_id' => 0,
                'subject' => 'Message Subject',
                'content' => 'Lorem ipsum',
                'read' => 1,
                'created_at' => '2017-12-01 13:00:00',
                'updated_at' => '2017-12-01 13:00:00'
            ],
            [
                'sender_id' => 2,
                'recipient_id' => 1,
                'reply_to_id' => 1,
                'subject' => 'Message Subject',
                'content' => 'Lorem ipsum',
                'read' => 1,
                'created_at' => '2017-12-01 13:00:00',
                'updated_at' => '2017-12-01 13:00:00'
            ],
            [
                'sender_id' => 2,
                'recipient_id' => 3,
                'reply_to_id' => 0,
                'subject' => 'Message Subject',
                'content' => 'Lorem ipsum',
                'read' => 0,
                'created_at' => '2017-12-01 12:00:00',
                'updated_at' => '2017-12-01 12:00:00'
            ],
            [
                'sender_id' => 1,
                'recipient_id' => 2,
                'reply_to_id' => 6,
                'subject' => 'Message Subject',
                'content' => 'Lorem ipsum',
                'read' => 1,
                'created_at' => '2017-12-01 14:00:00',
                'updated_at' => '2017-12-01 14:00:00'
            ],
        ]);
        
    }
}