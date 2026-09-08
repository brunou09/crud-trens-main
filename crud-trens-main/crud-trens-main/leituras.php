<?php

session_start();

require 'conexao.php';
//require 'limites.php';

$idTrem = (int) ($_GET['id_trem'] ?? 0);
$somenteFalha = isset($_GET['somente_falha']);

$mensagem = $_SESSION['mensagem'] ?? '';
unset($_SESSION['mensagem']);

$trens = $conexao->query('SELECT id_trem, prefixo_trem, modelo_trem FROM trens ORDER BY prefixo_trem');

if($idTrem > 0) {
    $stmt = $conexao->prepare('SELECT leitura_sensor.*, trens.modelo_trem, trens.prefixo_trem FROM leitura_sensor INNER JOIN trens ON trens.id_trem = leitura_sensor. fk_id_trem 
    WHERE leitura_sendor.fk_id_trem = ? ORDER BY leitura_sensor.data_hora DESC LIMIT 200');

    $stmt-> bind_param('i', $idTrem);
    $stmt-> exeulte();
    $leituras = $stmt->get_result();
}else{
    $leituras = $conexao->query('SELECT leitura_sensor.*, trens.modelo_trem, trens.prefixo_trem FROM leitura_sensor INNER JOIN trens ON trens.id_trem = leitura_sensor.fk_id_trem ORDER BY leitura_sensor.data_hora DESC LIMIT 200');
}
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
        <nav>
            <a href="index.php">Trens</a>
            <a href="painel.php">Painel</a>
            <a href="leituras.php">Leituras</a>
            <a href="Simulador.php">Simulador</a> 
        </nav>
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
    <?php
        if ($leituras->num_rows === 0):
    ?>
        <p class="vazio">Nenhuma leitura registrada. Use o simulador para gerar leituras.</p>
    <?php
        else:
    ?>
        <table>
            <thead>
                <tr>
                    <th>Data e hora</th>
                    <th>Trem</th>
                    <th>Velocidade</th>
                    <th>Temperatura</th>
                    <th>Consumo</th>
                    <th>Vibração</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    while ($linha = $leituras->fetch_assoc()):
                ?>
                <?php
                    $falhas = classificarLeitura($linha, $limiteVelocidade, $limiteTemperatura, $limiteConsumo, $limiteVibracao);

                    if ($somenteFalha && count($falhas) === 0){
                        continue;
                    }
                ?>
                <tr>
                    <td><?= date('d/m/Y:i', strototime($linha['data_hora'])) ?></td>
                    <td><?= htmlspecialchars($linha['prefixo_trem']) ?></td>
                    <td class="<?= $linha['velocidade_kmh'] > $limiteVelocidade ? 'acima' : '' ?>">
                        <?=number_format((float) $linha['velocidade_kmh'], 2, ',', '.') ?> km/h
                    </td>
                    <td class="<?= $linha['temperatura_motor_c'] > $limiteTemperatura ? 'acima' : '' ?>">
                        <?=number_format((float) $linha['temperatura_motor_c'], 2, ',', '.') ?> °C
                    </td>
                    <td class="<?= $linha['consumo_litros_hora'] > $limiteConsumo ? 'acima' : '' ?>">
                        <?=number_format((float) $linha['consumo_litros_hora'], 2, ',', '.') ?> L/h
                    </td>
                    <td class="<?= $linha['vibracao_mm_s'] > $limiteVibracao ? 'acima' : '' ?>">
                        <?=number_format((float) $linha['vibracao_mm_s'], 2, ',', '.') ?> mm/s
                    </td>
                </tr>
                <?php
                    endwhile;
                ?>
            </tbody>
        </table>  
    <?php
        endif;
    ?>          
    </main>
</body>
</html>
