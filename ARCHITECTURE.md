# Architecture

[Русская версия](ARCHITECTURE.ru.md)

## Website for users

Use widgets and helpers to prevent duplication and copying of code. Widgets and helpers help you reuse existing code and quickly and safely add new functionality.

## Telegram Bot

### File Structure

- [`/modules/bot`](/modules/bot)
  - [`/components`](/modules/bot/components) - Main bot components responsible for its functionality.
    - [`/actions`](/modules/bot/components/actions) - Actions shared among some controllers.
      - [`/privates`](/modules/bot/components/actions/privates) - Actions shared for private chats.
    - [`/api`](/modules/bot/components/api) - Classes for interacting with the Telegram API.
      - [`/Types`](/modules/bot/components/api/Types) - Classes for interacting with objects provided by the Telegram API.
        - [`Update`](/modules/bot/components/api/Types/Update) - Class for interacting with `Update` objects provided by the Telegram API.
      - [`BotApi`](/modules/bot/components/api/BotApi.php) - Class for interacting with the Telegram API.
    - [`/crud`](/modules/bot/components/crud) - Components related to CRUD (Create, Read, Update, Delete) operations for `CrudController`.
      - [`/rules`](/modules/bot/components/crud/rules) - Rules defining data access.
      - [`/services`](/modules/bot/components/crud/services) - Services for data processing.
    - [`CrudController`](/modules/bot/components/crud/CrudController.php) - Base controller class for controllers providing P2P services.
    - [`/helpers`](/modules/bot/components/helpers) - Classes for auxiliary functions.
    - [`/request`](/modules/bot/components/request) - Classes for handling requests and incoming data.
    - [`/response`](/modules/bot/components/response) - Classes for sending responses and outgoing data.
      - [`/commands`](/modules/bot/components/response/commands) - Classes for preparing responses of different types.
    - [`ResponseBuilder`](/modules/bot/components/ResponseBuilder.php) - Class for preparing responses of the appropriate type.
    - [`ChannelRouteResolver`](/modules/bot/components/ChannelRouteResolver.php) - Component for routing requests in channels.
    - [`Controller`](/modules/bot/components/Controller.php) - Base controller class containing common basic logic for regular controllers.
    - [`GroupRouteResolver`](/modules/bot/components/GroupRouteResolver.php) - Component for routing requests in groups.
    - [`PrivateRouteResolver`](/modules/bot/components/PrivateRouteResolver.php) - Component for routing requests in private chats.
  - [`/config`](/modules/bot/config) - Configuration files for module setup.
    - [`actions`](/modules/bot/config/actions.php) - An array with indexes instead of action names.
    - [`channels`](/modules/bot/config/channels.php) - Routing settings for channels.
    - [`controllers`](/modules/bot/config/controllers.php) - An array with indexes instead of controller names.
    - [`groups`](/modules/bot/config/groups.php) - Routing settings for groups.
    - [`privates`](/modules/bot/config/privates.php) - Routing settings for private chats.
  - [`/controllers`](/modules/bot/controllers) - Controllers for handling requests and managing bot functionality.
    - [`/channels`](/modules/bot/controllers/channels) - Controllers for working with channels.
    - [`/groups`](/modules/bot/controllers/groups) - Controllers for working with groups.
    - [`/privates`](/modules/bot/controllers/privates) - Controllers for working with private chats.
  - [`/filters`](/modules/bot/filters) - Filters used to process requests before passing them to controllers.
  - [`/models`](/modules/bot/models) - Data models, including database queries.
    - [`/queries`](/modules/bot/models/queries) - Queries used for data retrieval and processing.
  - [`/validators`](/modules/bot/validators) - Validators for checking incoming data.
  - [`/views`](/modules/bot/views) - Views for displaying information in chats.
    - [`/channels`](/modules/bot/views/channels) - Views for channels.
    - [`/groups`](/modules/bot/views/groups) - Views for groups.
    - [`/privates`](/modules/bot/views/privates) - Views for private chats.
  - [`Module`](/modules/bot/Module.php) - The main component of the module that configures and manages all other components.
  - [`WebHookAction`](/modules/bot/WebHookAction.php) - A class designed to handle incoming requests (webhooks) from the Telegram API.

### Request Processing Flow

#### 1. Receiving a Request - Update

The bot expects 2 types of `Update` objects from the Telegram API using Webhook:

- [`CallbackQuery`](/modules/bot/components/api/Types/CallbackQuery.php) - An object representing a request in response to pressing an inline button.
- [`Message`](/modules/bot/components/api/Types/Message.php) - An object representing a user's message.

#### 2. Request Routing

The request passes through one of the routing components, depending on the chat type, which determines what actions to perform and creates a route to the corresponding controller and action. The routing component uses routing settings to determine the mapping between commands and routes. Routing settings define routing rules that allow determining which commands are sent to which controllers and actions.

The routing component enables the bot to process commands sent by users and perform corresponding actions based on routing rules. If a command is not found, the request falls back to the default route.

###### Command Structure from a Message

Commands are messages that start with a command in the form of `/controller_name__action_name?key1=value1&key2=value2 text`.

- `/` - The slash indicates the start of the command.
- `controller_name` - The name of the controller that will handle the command.
- `action_name` (optional) - The name of the action to be executed in the controller. If not specified, the default action is used (e.g., `index`).
- `?key1=value1&key2=value2` (optional) - Command parameters passed as part of the command. These parameters can be used to provide additional information or settings. These parameters are passed to the `Request` object.
- `text` (optional) - A variable that is passed to the controller and contains all the text following the command. This allows controllers to process user-entered text.

Indexed arrays used instead of controller and action names are used to match numeric values in routing rules to actual controller and action names. For example, if a command contains a numeric code instead of a controller or action name, the numeric code will be converted to the corresponding controller or action name.

#### 3. Request Execution

The `Module` calls the corresponding controller and action based on the received route. All controller actions return an array of objects derived from the `Command` class.

After executing the action, the `Module` sends each command, which represents requests to the Telegram API.

The `Command` class is a class that knows how to send a specific request to the Telegram API.

#### 4. Sending a Request to the Telegram API

After creating a response, it is sent to the Telegram API through the `BotApi` component.

#### 5. Clearing Previous Messages (for private chats)

In private chats, the bot automatically deletes its previous messages unrelated to the current request and also deletes all user messages. To display a bot's message, the `editMessageTextOrSendMessage` command is used. When it is necessary to send multiple messages (e.g., first display a "Your location" text message, then send a location message, and then send a keyboard message), the `editMessageTextOrSendMessage` command should be called first, followed by the `sendLocation` and `sendMessage` commands.

The module itself has four fields that identify the user and may be useful to you: `update`, `user`, `telegramUser`, and `telegramChat`. For ease of access, methods for obtaining data from these fields are defined in the base controller class.

### Callback Data

Values for callback buttons should be within the range of 1-64 bytes. [Learn more](https://core.telegram.org/bots/api#inlinekeyboardbutton).

This limitation is circumvented by using array indexes instead of controller and action names in configuration files.

## Core

- `FLOAT`
  - Working with `float` in PHP always use [BC Math Functions](https://www.php.net/manual/en/ref.bc.php).
    - For handy comparison of two floats use `\app\helpers\Number`. [Why it so important?](https://stackoverflow.com/questions/3148937/compare-floats-in-php)
    - If DB table has decimal column - add trait `\app\models\traits\FloatAttributeTrait` into `ActiveRecord`.
    - Beware the same problem in all program languages ([MySql](https://stackoverflow.com/questions/2188139/check-for-equality-on-a-mysql-float-field), [JS](https://stackoverflow.com/questions/3343623/javascript-comparing-two-float-values/3343658), etc.)
