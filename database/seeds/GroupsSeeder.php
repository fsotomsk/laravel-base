<?php

class GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        if (!\CDeep\Models\Group::find(1)) {

            $groups = [
                [
                    'id'       => 1,
                    'name'     => 'wheel',
                ],
                [
                    'id'       => 10000,
                    'name'     => 'clients',
                ]
            ];

            foreach ($groups as $group) {
                (new \CDeep\Models\Group($group))->save();
            }
        }
    }
}
