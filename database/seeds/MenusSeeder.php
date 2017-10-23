<?php

class MenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $menu = \CDeep\Models\Menu::find(1);
        if (!$menu) {
            $menus = [
                [
                    'id' => 1,
                    'name' => 'Основное меню - шапка',
                    'page_ids' => [
                        10010, // О сервисе
                        10040, // Тарифы
                        10050, // Зарегистрироваться
                    ]
                ],
                [
                    'id' => 2,
                    'name' => 'Основное меню - подвал',
                    'page_ids' => [
                        10500, // Поддержка
                        10501, // Условия
                        10502, // Связь
                    ]
                ],
            ];

            foreach ($menus as $m) {
                $menu = new \CDeep\Models\Menu($m);
                $menu->save();
            }
        }
    }
}
