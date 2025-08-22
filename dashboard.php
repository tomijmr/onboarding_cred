<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Consulta BCRA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 400px;
            text-align: center;
        }
        input[type="text"], input[type="tel"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Consulta Central de Deudores (BCRA)</h2>
    <form action="admin_consultas.php" method="get">
        <button type="submit">Ver Consultas</button>
    </form> <br>
            <form action="admin_config.php" method="get">
        <button type="submit">Admin Config</button>
        </form>
    <form action="result.php" method="get">
        <input type="text" name="cuit" placeholder="Ingrese CUIT/CUIL/CDI" maxlength="11" required>
        <br>
        <input type="tel" name="telefono" placeholder="Ingrese teléfono" required>
        <br>
        <button type="submit">Consultar</button>
    </form>
</div>
</body>
</html>
