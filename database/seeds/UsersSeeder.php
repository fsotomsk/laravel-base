<?php

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        if (!\CDeep\Models\User::find(1)) {

            $users = [
                [
                    'id'        => 1,
                    'group_id'  => 1,
                    'email'     => 'root@localhost',
                    'name'      => 'Charlie Root',
                    'password'  => Hash::make('toor'),
                    'api_token' => 'KKOkIbOJXh07TA0DLEqq1S1LHKUx1BP4CdsLiiFU3bWg185iTERH1hBoeTTO',
                ],
                [
                    'id'        => 10000,
                    'group_id'  => 10000,
                    'email'     => 'test@localhost',
                    'name'      => 'test',
                    'password'  => Hash::make('tset'),
                    'api_token' => 'dXpOt9h1t3ZnFFjKmHh0JNz9621y3cdyWCPCMb7CrgvQa5D0N5lBNQXSgVpo',
                ],
            ];

            foreach ($users as $user) {
                $u = new \CDeep\Models\User($user);
                $u->save();
            }

        }
    }
}
