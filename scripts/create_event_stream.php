<?

declare(strict_types=1);

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;

require_once __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../config/container.php';

$eventStore = $container->get(EventStore::class);

$eventStore->create(new Stream(new StreamName('event_stream'), new ArrayIterator()));

echo 'done.';
