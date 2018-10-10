Example usage:

```$host = '127.0.0.1';
$db   = 'test';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
     throw new PDOException($e->getMessage(), (int)$e->getCode());
}

require_once('pdoutil.php');

$query = "SELECT user FROM users WHERE name IN :employee[] AND job = :occupation";

$p = new PDOUtil($query);

$p->add_data('employee[]', array('Alice', 'Bob'));
$p->add_data('occupation', 'developer');

$p->finalize();

$stmt = $pdo->prepare($p->get_query());
$stmt->execute($p->get_params());
$user = $stmt->fetch();
```




