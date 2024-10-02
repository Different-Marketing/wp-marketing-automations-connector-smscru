=== Autonami Marketing Automations Connector for SMSC.ru ===
Contributors: redmonkey73, claudeai
Donate link: https://my.mamatov.club/
Tags: autonami, marketing, automation, sms, smsc.ru
Requires at least: 4.9
Tested up to: 6.1.1
Stable tag: 2.1.0
Requires PHP: 7.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Интеграция SMSC.ru для Autonami Marketing Automations. Отправляйте SMS в ваших автоматизациях с помощью SMSC.ru.

== Description ==

Этот плагин добавляет интеграцию SMSC.ru в Autonami Marketing Automations для WordPress. С его помощью вы сможете отправлять SMS-сообщения через сервис SMSC.ru в рамках ваших маркетинговых автоматизаций.

Основные возможности:

* Отправка SMS через SMSC.ru в автоматизациях Autonami
* Поддержка персонализации сообщений с использованием тегов слияния Autonami
* Возможность отправки тестовых SMS
* Поддержка UTM-меток для отслеживания

Требования:

* WordPress 4.9 или выше
* Autonami Marketing Automations
* Активная учетная запись SMSC.ru

== Installation ==

1. Загрузите папку `autonami-automations-connectors-smscru` в директорию `/wp-content/plugins/` вашего сайта WordPress.
2. Активируйте плагин через меню 'Плагины' в WordPress.
3. Перейдите в настройки Autonami и подключите ваш аккаунт SMSC.ru.

== Frequently Asked Questions ==

= Нужна ли мне учетная запись SMSC.ru для использования этого плагина? =

Да, вам потребуется активная учетная запись SMSC.ru для отправки SMS через их сервис.

= Как настроить плагин? =

1. В админ-панели WordPress перейдите в раздел Autonami -> Настройки -> Интеграции.
2. Найдите SMSC.ru в списке интеграций и нажмите "Подключить".
3. Введите ваш логин и пароль от SMSC.ru.
4. Нажмите "Сохранить".

= Как использовать плагин в автоматизациях? =

После настройки вы сможете использовать действие "Отправить SMS через SMSC.ru" в ваших автоматизациях Autonami. При создании этого действия вы сможете указать получателя, текст сообщения и другие параметры.

== Screenshots ==

1. Настройки интеграции SMSC.ru
2. Создание действия отправки SMS в автоматизации
3. Пример отправленного SMS

== Changelog ==

= 2.0.8 =
* Улучшена обработка ошибок при отправке SMS
* Добавлена поддержка новых тегов слияния Autonami

= 2.0.7 =
* Исправлены мелкие баги
* Улучшена производительность

= 2.0.0 =
* Первый публичный релиз

== Upgrade Notice ==

= 2.0.8 =
Это обновление улучшает обработку ошибок и добавляет поддержку новых тегов слияния. Рекомендуется всем пользователям.

== Структура плагина ==

<pre><code>
wp-marketing-automations-connector-smscru/ 
│ ├── autonami/ │ 
├── actions/ │ 
│ └── class-bwfan-smscru-send-sms.php 
│ └── class-bwfan-smscru-integrations.php │ 
├── calls/ 
│ ├── class-wfco-smscru-get-balance.php 
│ └── class-wfco-smscru-send-sms.php │ 
├── includes/ 
│ ├── class-wfco-smscru-call.php 
│ ├── js/ 
│ └── smscru-test.js 
| ├── views/ 
│ └── settings.php 
│ ├── node_modules/ 
│ ├── class-bwfan-smscru-test-integration.php 
├── connector.php 
├── index.php 
├── README.md 
└── wp-marketing-automations-connector-smscru.php
</code></pre>

Эта структура показывает основные файлы и папки плагина.

== Arbitrary section ==

Вы можете найти более подробную информацию о разработке и использовании плагина на нашем [сайте поддержки](https://my.mamatov.club/).

== A brief Markdown Example ==

Заголовки:

# H1
## H2
### H3

Списки:

* Пункт 1
* Пункт 2
  * Подпункт 2.1
  * Подпункт 2.2

1. Первый пункт
2. Второй пункт

Ссылки и изображения:

[Ссылка на WordPress](https://wordpress.org)

![Логотип WordPress](https://s.w.org/style/images/about/WordPress-logotype-standard.png)

Подробнее о синтаксисе Markdown вы можете узнать [здесь](https://daringfireball.net/projects/markdown/syntax).