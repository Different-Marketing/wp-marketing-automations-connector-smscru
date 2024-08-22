<!-- ⚠️ This README has been generated from the file(s) "blueprint.md" ⚠️-->
[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#autonami-marketing-automations-connector-for-smscru)

# ➤ Autonami Marketing Automations Connector for SMSC.ru


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Описание

Этот плагин добавляет интеграцию SMSC.ru в Autonami Marketing Automations для WordPress. С его помощью вы сможете отправлять SMS-сообщения через сервис SMSC.ru в рамках ваших маркетинговых автоматизаций.


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Возможности

- Отправка SMS через SMSC.ru в автоматизациях Autonami
- Поддержка персонализации сообщений с использованием тегов слияния Autonami
- Возможность отправки тестовых SMS
- Поддержка UTM-меток для отслеживания


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Требования

- WordPress 4.9 или выше
- Autonami Marketing Automations
- Активная учетная запись SMSC.ru


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Установка

1. Загрузите папку `autonami-automations-connectors-smscru` в директорию `/wp-content/plugins/` вашего сайта WordPress.
2. Активируйте плагин через меню 'Плагины' в WordPress.
3. Перейдите в настройки Autonami и подключите ваш аккаунт SMSC.ru.


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Настройка

1. В админ-панели WordPress перейдите в раздел Autonami -> Настройки -> Интеграции.
2. Найдите SMSC.ru в списке интеграций и нажмите "Подключить".
3. Введите ваш логин и пароль от SMSC.ru.
4. Нажмите "Сохранить".


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Использование

После настройки вы сможете использовать действие "Отправить SMS через SMSC.ru" в ваших автоматизациях Autonami. При создании этого действия вы сможете указать получателя, текст сообщения и другие параметры.


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Поддержка

Если у вас возникли проблемы с использованием плагина, пожалуйста, создайте issue в репозитории GitHub или обратитесь в нашу службу поддержки.


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Лицензия

Этот плагин распространяется под лицензией GPL v2 или более поздней версии.


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Авторы

Разработано командой my.mamatov.club.


[![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/colored.png)](#)

## ➤ Благодарности

Особая благодарность команде Autonami за создание отличной платформы для маркетинговых автоматизаций.
wp-marketing-automations-connector-smscru/
│
├── autonami/
│   ├── actions/
│   │   └── class-bwfan-smscru-send-sms.php
│   └── class-bwfan-smscru-integrations.php
│
├── calls/
│   ├── class-wfco-smscru-get-balance.php
│   └── class-wfco-smscru-send-sms.php
│
├── includes/
│   ├── class-wfco-smscru-call.php
│
├── js/
│   └── smscru-test.js
|
├── views/
│   └── settings.php
│
├── node_modules/
│
├── class-bwfan-smscru-test-integration.php
├── connector.php
├── index.php
├── README.md
└── wp-marketing-automations-connector-smscru.php