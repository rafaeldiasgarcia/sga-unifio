# **SGA - Sistema de Gerenciamento de Atléticas e Agendamentos**

## **Visão Geral do Projeto**

O **SGA** é um sistema web completo projetado para gerenciar as atividades esportivas e administrativas de atléticas universitárias, além de controlar o agendamento de espaços físicos como quadras poliesportivas. O sistema foi desenvolvido para a UNIFIO, com múltiplos níveis de acesso e funcionalidades específicas para cada perfil de usuário.

Este projeto foi construído utilizando:
*   **Backend:** PHP
*   **Frontend:** HTML5, CSS3, Bootstrap 5
*   **Banco de Dados:** MySQL
*   **Ambiente de Desenvolvimento:** XAMPP (Apache, MySQL, PHP)

---

## **Estrutura de Perfis e Funcionalidades**

O sistema possui 4 perfis de usuário distintos, cada um com seu próprio conjunto de permissões e responsabilidades:

### 1. **Usuário Comum (Aluno, Membro de Atlética, Professor, Comunidade Externa)**
*   **Registro Detalhado:** Formulário de registro inteligente que se adapta ao tipo de vínculo com a instituição, com validação de e-mail institucional para membros internos.
*   **Login Seguro:** Autenticação em duas etapas com e-mail/senha e um código de verificação.
*   **Painel Pessoal (`Meu Painel`):** Dashboard adaptativo que mostra informações relevantes para o perfil do usuário.
*   **Gerenciamento de Perfil:** Página completa para editar dados pessoais (nome, e-mail, curso) e alterar a senha.
*   **Visualização da Agenda:** Acesso à agenda pública da quadra para ver todos os eventos aprovados.

#### Funcionalidades Específicas:
*   **Aluno:** Pode solicitar a entrada na atlética associada ao seu curso.
*   **Membro de Atlética:** Pode se inscrever em modalidades esportivas e sair da atlética.
*   **Professor:** Pode criar solicitações de agendamento de eventos (esportivos e não esportivos) e acompanhar o status de aprovação.

### 2. **Admin da Atlética**
*   **Painel de Controle:** Dashboard com resumo de inscrições pendentes, total de atletas e solicitações para se tornar membro.
*   **Gerenciamento de Membros:** Aprova ou recusa solicitações de alunos que desejam entrar na atlética.
*   **Gerenciamento de Inscrições:** Aprova ou recusa a participação de membros em modalidades esportivas específicas.
*   **Montagem de Equipes:** Ferramenta completa para criar equipes dentro de cada modalidade e alocar/remover membros.

### 3. **Super Administrador**
*   **Controle Total (CRUD):** Acesso completo para Criar, Ler, Editar e Excluir todos os dados estruturais do sistema:
    *   Gerenciamento de Atléticas
    *   Gerenciamento de Cursos (e sua associação com atléticas)
    *   Gerenciamento de Eventos (ex: Intercursos)
    *   Gerenciamento de Modalidades
*   **Gerenciamento de Usuários:**
    *   Visualiza a lista de todos os usuários cadastrados.
    *   Pode editar 100% das informações de qualquer usuário, incluindo a redefinição de senha e a mudança de perfil (ex: promover um Aluno a Admin).
    *   Pode excluir usuários com confirmação por senha.
*   **Aprovação de Agendamentos:** Painel para aprovar ou rejeitar as solicitações de uso da quadra feitas pelos professores, com a obrigatoriedade de fornecer um motivo para a rejeição.

---

## **Instalação e Configuração do Ambiente**

Para que o sistema funcione corretamente, ele precisa ser executado em um ambiente de servidor local. O XAMPP é a ferramenta recomendada para isso.

1.  **Pré-requisitos:**
    *   Ter o [XAMPP](https://www.apachefriends.org/index.html) instalado em seu computador.
    *   Abrir o Painel de Controle do XAMPP e garantir que os módulos **Apache** e **MySQL** estejam em execução (indicados pela cor de fundo verde).

2.  **Estrutura de Arquivos:**
    *   Para que o servidor Apache possa encontrar e executar os arquivos do projeto, eles **precisam** estar localizados dentro de uma pasta específica do XAMPP.
    *   Navegue até o diretório de instalação do XAMPP (normalmente `C:\xampp\`) e encontre a pasta chamada **`htdocs`**.
    *   Dentro de `htdocs`, crie uma nova pasta e nomeie-a como **`sga`**.
    *   O caminho final para o seu projeto deve ser: `C:\xampp\htdocs\sga`.
    *   Copie todos os arquivos e pastas do projeto para dentro deste diretório.

3.  **Banco de Dados:**
    *   Abra seu navegador e acesse o painel de gerenciamento do banco de dados: `http://localhost/phpmyadmin`.
    *   Crie um novo banco de dados com o nome exato **`sga_db`** e selecione o agrupamento (collation) `utf8mb4_general_ci`.
    *   Selecione o banco de dados recém-criado, vá para a aba "SQL" e execute todos os comandos SQL fornecidos durante o desenvolvimento para criar a estrutura de tabelas e inserir os dados iniciais.

4.  **Acesso ao Sistema:**
    *   Com o ambiente configurado, abra seu navegador e acesse o seguinte endereço: `http://localhost/sga`.

---

## **Credenciais de Acesso para Testes**

*   **Super Administrador:**
    *   **Email:** `super@unifio.edu.br`
    *   **Senha:** `super123` (ou a que foi definida durante os testes)

*   **Admin da Atlética:**
    *   Crie um usuário "Aluno" através do registro público.
    *   Faça login como Super Admin e promova este aluno a "Admin da Atlética" na seção "Gerenciar Admins".

*   **Outros Perfis:**
    *   Utilize a página de registro (`http://localhost/sga/registro.php`) para criar usuários com os perfis de Aluno, Professor, etc.

---

## **Status Atual dos Testes e Próximos Passos**

**Data do Último Teste:** `13/09/2025`

**Checklist de Testes (Resumo):**
- [ ] **Fluxo de Registro:** Todos os perfis registrando corretamente.
- [ ] **Fluxo de Login:** Login com senha e 2FA funcionando para todos (codigo chega pelo prorpio site, por enquanto é apenas um alert, nao envia ao seu email).
- [ ] **Painel do Aluno:** Inscrição em modalidade e solicitação para entrar na atlética.
- [ ] **Painel do Professor:** Criação de agendamento e visualização de status.
- [ ] **Painel do Admin da Atlética:** Aprovação de membros e inscrições; montagem completa de equipes (criar, alocar, remover, excluir).
- [ ] **Painel do Super Admin:** CRUD completo de Atléticas, Cursos, etc. Gerenciamento e exclusão de usuários. Aprovação/Rejeição de agendamentos com motivo.

### **Onde Eu Parei:**



> Estou continuando testes, ultima coisa que fiz foi testar o fluxo de criação e gerenciamento de equipes pelo Admin da Atlética. Tudo está funcionando conforme o esperado até agora, mas preciso continuar os testes nos outros tipos de perfis para garantir que todas as funcionalidades estejam operacionais.
---