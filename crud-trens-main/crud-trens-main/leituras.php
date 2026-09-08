<?php

session_start();

require 'conexao.php';
//require 'limites.php';

$idTrem = (int) ($_GET['id_trem'] ?? 0);
$somenteFalha = isset($_GET['somente_falha']);

$mensagem = $_SESSION['mensagem'] ?? '';
unset($_SESSION['mensagem']);

$trens = $conexao->query('SELECT id_trem, prefixo_trem, modelo_trem FROM trens ORDER BY prefixo_trem');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leituras dos sensores</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <head>
        <span class="marca">Frota Ferroviária</span>
    </head>

    <main>
        <div class="titulo">
            <h1>Leituras dos sensores</h1>
        </div>

        <?php
            if ($mensagem !== ''):
        ?>
            <p class="aviso"><?= htmlspecialchars($mensagem) ?></p>
        <?php
            endif;
        ?>

        <form method="GET" class="formulario">
            <div class="linha">
                <div class="campo">
                    <label for="id_trem">Filtrar por trem</label>
                    <select id="id_trem" name="id_trem">
                        <option value="0">Todos os trens</option>
                        <?php
                            while ($trem = $trens->fetch_assoc()):
                        ?>
                            <option value="<?=  (int) $trem['id_trem'] ?>">
                                <?=  htmlspecialchars($trem['prefixo_trem']) ?> - <?= htmlspecialchars($trem['modelo_trem']) ?>
                            </option>
                        <?php
                            endwhile;
                        ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="somente_falha">Exibição</label>
                    <label for="opcao">
                        <input type="checkbox" id="somente_falha" name="somente_falha" <?= $somenteFalha ? 'checked' : '' ?>>
                        Mostrar somente leituras com falha
                    </label>
                </div>
            </div>

            <div class="acoes">
                <button type="submit" class="botao botao-primario">Filtrar</button>
                <a href="leituras.php" class="botao botao-secundario">Limpar</a>
            </div>
        </form>
    </main>
</body>
</html>