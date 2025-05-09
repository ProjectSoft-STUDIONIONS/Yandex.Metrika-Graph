# Widget: Yandex.Metrika Graph #

Виджет посещаемости сайта. Отображает график Яндекс.Метрики на стартовой странице админки Evolution CMS.

## Настройка ##
* Установить виджет через репозиторий.
* <a href="https://oauth.yandex.ru/client/new" target="_blank">Создать приложение</a> на Oauth.Yandex. 
* Дать ему права на Яндекс.Метрику. Внимание! Не перепутайте с AppMetrika!
* В секции "Платформы" выбрать "Веб-сервисы", в поле `Callback URI #1` подставить URL для разработки  `https://oauth.yandex.ru/verification_code`
* Зайти в "Элементы - Плагины, выбрать "Widget: Yandex.Metrika Graph"
* Открыть вкладку "Конфигурация"
* Вставить в поле "ID приложения" полученный ID приложения.
* Сохранить плагин
* Перейти на главную страницу админки, нажать на ссылку "Получить доступ к счётчику". Скопировать полученный токен и вставть его в конфигурацию плагина.
* После этого служебные ссылки можно скрыть

<img src="https://github.com/0test/Yandex.Metrika-Graph/blob/master/screen.png?raw=true">

## Update

**Плагин пересобран.**

Есть отличия в работе js плагина от исходного https://github.com/0test/Yandex.Metrika-Graph плагина

```diff
+ Добавлен скрипт `highcharts.js` в файлы и задано его подключение. Последнее время не подключался скрипт с CDN.
+ Добавлен кастомный тултип для удобства просмотра.
+ Добавлена возможность задать период последних дней.
+ Добавлена возможность задать анимацию при инициализации графика.
```
