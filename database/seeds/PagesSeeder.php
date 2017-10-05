<?php

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $page = \App\Models\Page::find(1);
        if (!$page) {
            $pages = [
                ['id' => 1,     'owner_user_id' => 1, 'owner_group_id' => 1, 'uri' => 'index', 'title' => 'cDeep Site', 'topic' => 'cDeep Site', 'menu' => 'Home', 'keywords' => '', 'description' => '', 'template_resource' => 'site', 'template_env' => '_env/index', 'template_view' => 'main/index', 'show_in_menu' => 1, 'menu_sort_order' => 100, 'is_published' => 1, 'is_enabled' => 1,],

                ['id' => 2,     'owner_user_id' => 1, 'owner_group_id' => 1, 'page_id' => 1, 'uri' => 'sadm',
                    'title' => 'Администрирование', 'topic' => 'Администрирование', 'menu' => 'Администрирование', 'keywords' => '', 'description' => '',
                    'template_resource' => 'sadm', 'template_env' => '_env/index', 'template_view' => 'main/index',
                    'show_in_menu' => 0, 'menu_sort_order' => 100, 'is_published' => 1, 'is_enabled' => 1,],
                ['id' => 9999,  'owner_user_id' => 1, 'owner_group_id' => 1, 'page_id' => 2, 'uri' => 'logout',
                    'title' => 'Выход', 'topic' => 'Выход', 'menu' => 'Выход', 'keywords' => '', 'description' => '',
                    'template_view' => 'errors/404',
                    'show_in_menu' => 0, 'menu_sort_order' => 100, 'is_published' => 1, 'is_enabled' => 1,],

                ['id' => 3,     'owner_user_id' => 1, 'owner_group_id' => 1, 'page_id' => 1, 'uri' => 'errors', 'title' => 'Ошибка', 'topic' => 'Ошибка', 'template_view' => 'errors/index',
                    'show_in_menu'  => 0,
                    'is_published'  => 1,
                    'is_enabled'    => 1
                ],
                ['id' => 401,   'owner_user_id' => 1, 'owner_group_id' => 1, 'page_id' => 3, 'uri' => '401',    'title' => 'Доступ ограничен', 'topic' => 'Доступ ограничен', 'template_view' => 'errors/401', 'is_published' => 1, 'is_enabled' => 1,],
                ['id' => 402,   'owner_user_id' => 1, 'owner_group_id' => 1, 'page_id' => 3, 'uri' => '403',    'title' => 'Доступ запрещен', 'topic' => 'Доступ запрещен', 'template_view' => 'errors/403', 'is_published' => 1, 'is_enabled' => 1,],
                ['id' => 404,   'owner_user_id' => 1, 'owner_group_id' => 1, 'page_id' => 3, 'uri' => '404',    'title' => 'Не найдено', 'topic' => 'Не найдено', 'template_view' => 'errors/404', 'is_published' => 1, 'is_enabled' => 1,],

                ['id' => 10001, 'owner_user_id' => 1, 'owner_group_id' => 1,
                    'page_id' => 1, 'uri' => 'signin',
                    'title'         => 'Вход',
                    'topic'         => 'Вход',
                    'menu'          => 'Вход',
                    'keywords'      => '',
                    'description'   => '',
                    'template_view' => 'signin/index',
                    'show_in_menu'  => 0,
                    'menu_sort_order' => 100,
                    'is_published'  => 1,
                    'is_enabled'    => 1,
                ],

                ['id' => 10010, 'owner_user_id' => 1, 'owner_group_id' => 1,
                    'page_id' => 1, 'uri' => 'about',
                    'title'         => 'О сервисе',
                    'topic'         => 'Что это такое?',
                    'menu'          => 'О сервисе',
                    'keywords'      => '',
                    'description'   => '', 
                    'template_view' => 'about/index',
                    'show_in_menu'  => 1,
                    'menu_sort_order' => 100,
                    'is_published'  => 1,
                    'is_enabled'    => 1,
                ],

                ['id' => 10015, 'owner_user_id' => 1, 'owner_group_id' => 1,
                    'page_id' => 10010, 'uri' => 'start',
                    'title'         => 'С чего начать?',
                    'topic'         => 'Инструкция для старта',
                    'menu'          => 'С чего начать?',
                    'keywords'      => '',
                    'description'   => '',
                    'template_view' => 'about/start',
                    'show_in_menu'  => 1,
                    'menu_sort_order' => 100,
                    'is_published'  => 1,
                    'is_enabled'    => 1,
                ],

                ['id' => 10040, 'owner_user_id' => 1, 'owner_group_id' => 1,
                    'page_id' => 1, 'uri' => 'prices',
                    'title'         => 'Тарифы',
                    'topic'         => 'Стоимость',
                    'menu'          => 'Тарифы',
                    'keywords'      => '',
                    'description'   => '',
                    'template_view' => 'prices/index',
                    'show_in_menu'  => 1,
                    'menu_sort_order' => 100,
                    'is_published'  => 1,
                    'is_enabled'    => 1,
                ],

                ['id' => 10050, 'owner_user_id' => 1, 'owner_group_id' => 1,
                    'page_id' => 1, 'uri' => 'signup',
                    'title'         => 'Регистрация',
                    'topic'         => 'Регистрация',
                    'menu'          => 'Зерегистрироваться',
                    'keywords'      => '',
                    'description'   => '',
                    'template_view' => 'signup/index',
                    'show_in_menu'  => 1,
                    'menu_sort_order' => 100,
                    'is_published'  => 1,
                    'is_enabled'    => 1,
                ],

                ['id' => 10500, 'owner_user_id' => 1, 'owner_group_id' => 1,
                    'page_id' => 1, 'uri' => 'help',
                    'title'         => 'Поддержка',
                    'topic'         => 'Поддержка',
                    'menu'          => 'Поддержка',
                    'keywords'      => '',
                    'description'   => '',
                    'template_view' => 'help/index',
                    'show_in_menu'  => 1,
                    'menu_sort_order' => 100,
                    'is_published'  => 1,
                    'is_enabled'    => 1,
                ],
                ['id' => 10501, 'owner_user_id' => 1, 'owner_group_id' => 1,
                    'page_id' => 1, 'uri' => 'terms',
                    'title'         => 'Условия использования',
                    'topic'         => 'Условия использования',
                    'menu'          => 'Условия использования',
                    'keywords'      => '',
                    'description'   => '',
                    'template_view' => 'help/terms',
                    'show_in_menu'  => 1,
                    'menu_sort_order' => 100,
                    'is_published'  => 1,
                    'is_enabled'    => 1,
                ],
                ['id' => 10502, 'owner_user_id' => 1, 'owner_group_id' => 1,
                    'page_id' => 1, 'uri' => 'feedback',
                    'title'         => 'Связь',
                    'topic'         => 'Связь',
                    'menu'          => 'Связь',
                    'keywords'      => '',
                    'description'   => '',
                    'template_view' => 'help/feedback',
                    'show_in_menu'  => 1,
                    'menu_sort_order' => 100,
                    'is_published'  => 1,
                    'is_enabled'    => 1,
                ],
            ];

            foreach ($pages as $p) {
                $page = new \App\Models\Page($p);
                $page->save();
            }
        }
    }
}
