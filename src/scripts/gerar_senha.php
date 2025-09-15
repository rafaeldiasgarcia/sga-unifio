<?php
// Script protegido para geração de senhas
// Acesso restrito apenas em ambiente de desenvolvimento

// Defina a senha que você quer usar
$senha = 'super123';

// Gera o hash (o código criptografado)
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Exibe o resultado na tela
echo "<h1>Gerador de Senha para Super Admin</h1>";
echo "<p><strong>Senha:</strong> " . htmlspecialchars($senha) . "</p>";
echo "<p><strong>Novo Hash Gerado (copie este código):</strong></p>";
echo '<textarea rows="4" cols="80" readonly onclick="this.select();">' . htmlspecialchars($hash) . '</textarea>';
echo "<p><em>IMPORTANTE: Este script deve ser removido em produção!</em></p>";
?>
