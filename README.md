# WPZylos Events

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-KYNetCode-181717?logo=github)](https://github.com/KYNetCode/wpzylos-events)

PSR-14 compliant event dispatcher for WPZylos framework.

📖 **[Full Documentation](https://wpzylos.com)** | 🐛 **[Report Issues](https://github.com/KYNetCode/wpzylos-events/issues)**

---

## ✨ Features

- **PSR-14 Compliant** — Implements `EventDispatcherInterface` and `ListenerProviderInterface`
- **Priority Listeners** — Control execution order with numeric priorities
- **Event Subscribers** — Group multiple listeners in a single class
- **Stoppable Events** — Halt propagation with `StoppableEvent` base class
- **Hierarchical Matching** — Listeners trigger for parent classes and interfaces
- **Container Integration** — Auto-registered via `EventServiceProvider`

---

## 📋 Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | ^8.0    |

---

## 🚀 Installation

```bash
composer require KYNetCode/wpzylos-events
```

---

## 📖 Quick Start

```php
use WPZylos\Framework\Events\EventDispatcher;
use WPZylos\Framework\Events\ListenerProvider;

// 1. Create the provider and dispatcher
$provider   = new ListenerProvider();
$dispatcher = new EventDispatcher($provider);

// 2. Register a listener on the provider
$provider->addListener(UserCreated::class, function (UserCreated $event) {
    mail($event->user->email, 'Welcome!', 'Thanks for signing up.');
});

// 3. Dispatch an event through the dispatcher
$dispatcher->dispatch(new UserCreated($user));
```

With the WPZylos container (after `EventServiceProvider` is registered):

```php
// Resolve from the container
$provider   = $app->make(ListenerProvider::class);
$dispatcher = $app->make('events'); // or EventDispatcherInterface::class

$provider->addListener(UserCreated::class, fn(UserCreated $e) => /* handle */);
$dispatcher->dispatch(new UserCreated($user));
```

---

## 🏗️ Core Concepts

### Event Classes

Events are plain PHP objects — no base class required:

```php
class UserCreated
{
    public function __construct(
        public readonly User $user
    ) {}
}

class OrderPlaced
{
    public function __construct(
        public readonly Order $order,
        public readonly User $customer
    ) {}
}
```

### Listeners

Register listeners on the `ListenerProvider`:

```php
// Closure listener
$provider->addListener(UserCreated::class, function (UserCreated $event) {
    mail($event->user->email, 'Welcome!', 'Thanks for signing up.');
});

// Class method listener
$provider->addListener(UserCreated::class, [new SendWelcomeEmail(), 'handle']);

// With priority (lower = earlier, default: 10)
$provider->addListener(UserCreated::class, $logCreation, 5);
```

### Subscribers

Group multiple listeners in a single class:

```php
use WPZylos\Framework\Events\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UserCreated::class => 'onUserCreated',
            UserDeleted::class => ['onUserDeleted', 5],
        ];
    }

    public function onUserCreated(UserCreated $event): void
    {
        // Handle creation
    }

    public function onUserDeleted(UserDeleted $event): void
    {
        // Handle deletion
    }
}

// Register through EventServiceProvider
$eventProvider = $app->make(EventServiceProvider::class);
$eventProvider->subscribe(new UserEventSubscriber());
```

### Stoppable Events

Extend the `StoppableEvent` abstract class to create events that can halt propagation:

```php
use WPZylos\Framework\Events\StoppableEvent;

class PaymentValidation extends StoppableEvent
{
    public bool $isValid = true;

    public function __construct(
        public readonly Payment $payment
    ) {}
}

// In a listener
$provider->addListener(PaymentValidation::class, function (PaymentValidation $event) {
    if ($event->payment->amount > 10000) {
        $event->isValid = false;
        $event->stopPropagation(); // Remaining listeners are skipped
    }
});
```

---

## 📦 Related Packages

| Package                                                                | Description            |
| ---------------------------------------------------------------------- | ---------------------- |
| [wpzylos-core](https://github.com/KYNetCode/wpzylos-core)         | Application foundation |
| [wpzylos-hooks](https://github.com/KYNetCode/wpzylos-hooks)       | WordPress hooks        |
| [wpzylos-scaffold](https://github.com/KYNetCode/wpzylos-scaffold) | Plugin template        |

---

## 📖 Documentation

For comprehensive documentation, tutorials, and API reference, visit **[wpzylos.com](https://wpzylos.com)**.

---

## ☕ Support the Project

- [GitHub Sponsors](https://github.com/sponsors/KYNetCode)
- [PayPal Donate](https://www.paypal.com/donate/?hosted_button_id=66U4L3HG4TLCC)

---

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

**Made with ❤️ by [KYNetCode](https://github.com/KYNetCode)**
