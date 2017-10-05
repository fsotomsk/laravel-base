<?php

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(GroupsSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(PagesSeeder::class);
        $this->call(MenusSeeder::class);
    }
}
