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

**Data do Último Teste:** `14/09/2025`

**Status:** Em andamento


### **Checklist Completo de Testes do Sistema SGA**

#### **Parte 1: Fluxo Público (Registro e Recuperação de Senha)**
*(Comece em uma janela anônima do navegador para garantir que não há nenhum usuário logado)*

*   **Teste 1.1: Registro de Professor (Múltiplos Cursos)**
    *   **Ação:** Acesse a página de **Registro**.
    *   Selecione o Vínculo: **"Professor"**.
    *   **Verificação:** O campo "RA/Matrícula" deve desaparecer. O campo "Quais cursos você dá aula?" deve aparecer.
    *   Preencha os dados usando um e-mail institucional (`@unifio.edu.br`). No campo de cursos, segure `Ctrl` (ou `Cmd`) e selecione **dois ou mais cursos**.
    *   **Resultado Esperado:** Registro concluído com sucesso. No banco de dados, na tabela `professores_cursos`, devem existir múltiplas entradas para o ID deste novo professor.

*   **Teste 1.2: Registro de Aluno (Validação de RA)**
    *   **Ação:** Tente registrar um "Aluno" com um RA de 5 dígitos (ex: `12345`).
    *   **Resultado Esperado:** O sistema deve exibir um erro informando que o RA precisa ter exatamente 6 números.
    *   **Ação:** Tente novamente com um RA de 6 dígitos (ex: `123456`).
    *   **Resultado Esperado:** Registro concluído com sucesso.

*   **Teste 1.3: Recuperação de Senha**
    *   **Ação:** Na página de **Login**, clique em **"Esqueci a senha"**.
    *   Digite o e-mail do Professor que você criou no Teste 1.1 e envie.
    *   **Resultado Esperado:** Uma mensagem de simulação de e-mail deve aparecer com um link para redefinir a senha.
    *   **Ação:** Clique no link, defina uma nova senha e salve.
    *   **Resultado Esperado:** Uma mensagem de sucesso deve aparecer.
    *   **Ação:** Tente fazer login com o Professor usando a **nova senha**.
    *   **Resultado Esperado:** O login deve funcionar perfeitamente.

---

#### **Parte 2: Fluxo do Usuário (Professor e Aluno)**

*   **Teste 2.1: Painel e Agendamento do Professor**
    *   **Ação:** Faça login com a conta do Professor.
    *   **Verificação:** O menu deve mostrar "Agendar Evento" e "Meus Agendamentos". O "Painel do Usuário" deve estar visível.
    *   **Ação:** Vá para **"Agendar Evento"** e crie uma nova solicitação.
    *   **Ação:** Vá para **"Meus Agendamentos"**.
    *   **Resultado Esperado:** O evento recém-criado deve aparecer na lista com o status **"Pendente"**.

*   **Teste 2.2: Painel do Aluno e Solicitação para Atlética**
    *   **Ação:** Faça login com uma conta de "Aluno" (que não seja "Membro das Atléticas"). Vá para **"Editar Perfil"**.
    *   **Verificação:** O card "Gerenciar Atlética" deve estar visível.
    *   **Ação:** Clique no botão verde **"Quero entrar na Atlética"**.
    *   **Resultado Esperado:** A página recarrega. A mensagem muda para "Sua solicitação para entrar na atlética está pendente de aprovação." e o botão desaparece.

*   **Teste 2.3: Edição de Perfil do Professor**
    *   **Ação:** Faça login com a conta do Professor. Vá para **"Editar Perfil"**.
    *   **Verificação:** O campo "Cursos que Leciono" deve estar visível e com os cursos corretos pré-selecionados.
    *   **Ação:** Mude a seleção de cursos (adicione ou remova um) e salve.
    *   **Resultado Esperado:** Os dados devem ser salvos com sucesso.

---

#### **Parte 3: Fluxo do Admin da Atlética**

*   **Teste 3.1: Aprovação de Novo Membro**
    *   **Ação:** Faça login com uma conta de **Admin da Atlética**.
    *   **Verificação:** No dashboard, o card **"Solicitações para Entrar"** deve mostrar um número maior que zero.
    *   **Ação:** Clique para ir à página **"Gerenciar Membros"**. A solicitação do Aluno (Teste 2.2) deve estar na lista.
    *   **Ação:** Clique em **"Aprovar"**.
    *   **Resultado Esperado:** A solicitação some da lista. No banco de dados, o `tipo_usuario_detalhado` daquele aluno agora é "Membro das Atléticas".

---

#### **Parte 4: Fluxo do Super Administrador**

*   **Teste 4.1: Gerenciamento de Coordenadores**
    *   **Ação:** Faça login como **Super Admin**. Vá para **"Gerenciar Usuários"** e edite a conta do Professor.
    *   **Ação:** Marque o checkbox **"Marcar como Professor Coordenador"** e salve.
    *   **Ação:** Vá para **"Gerenciar Cursos"** e edite um curso.
    *   **Verificação:** O nome do Professor agora deve aparecer na lista suspensa **"Coordenador do Curso"**.
    *   **Ação:** Associe o professor como coordenador daquele curso e salve.
    *   **Resultado Esperado:** A associação foi salva com sucesso.

*   **Teste 4.2: Verificação do Rodapé Dinâmico**
    *   **Ação:** Faça logout. Faça login com um Aluno que esteja matriculado no curso que você editou no teste anterior.
    *   **Resultado Esperado:** Role até o final da página. O rodapé agora deve mostrar a seção **"Coordenação"** com o nome e e-mail do professor coordenador.
    *   **Ação:** Faça logout e entre com um usuário da "Comunidade Externa".
    *   **Resultado Esperado:** A seção de coordenação no rodapé **não deve aparecer**.

*   **Teste 4.3: Teste de Conflito de Agendamento**
    *   **Setup:** Crie e aprove um evento para uma data e período específicos (ex: 25/12/2025, 1º Período). Crie uma segunda solicitação (de outro professor) para a mesma data e período.
    *   **Ação:** Como Super Admin, vá para **"Aprovar Agendamentos"** e tente aprovar a segunda solicitação.
    *   **Resultado Esperado:** A aprovação deve falhar e uma mensagem de erro sobre o conflito de horário deve ser exibida.

*   **Teste 4.4: Rejeição de Agendamento com Motivo**
    *   **Ação:** Na mesma página, clique em **"Rejeitar"** para uma solicitação pendente. Um pop-up deve aparecer.
    *   **Ação:** Preencha o motivo da rejeição e confirme.
    *   **Ação:** Faça login com o Professor que criou o evento e vá para **"Meus Agendamentos"**.
    *   **Resultado Esperado:** O evento deve estar com o status "Rejeitado" e o motivo que você escreveu deve estar visível.

---

#### **Parte 5: Teste Final de Presença**

*   **Teste 5.1: Marcar e Desmarcar Presença**
    *   **Ação:** Como um usuário qualquer, vá para a **"Agenda da Quadra"**.
    *   **Ação:** Encontre um evento aprovado e clique em **"Marcar Presença"**.
    *   **Resultado Esperado:** A página recarrega e o botão muda para **"Desmarcar Presença"**.
    *   **Ação:** Vá para o **"Painel do Usuário"**.
    *   **Resultado Esperado:** O evento deve aparecer na lista de **"Presenças Marcadas"**.
    *   **Ação:** Volte para a agenda e clique em **"Desmarcar Presença"**.
    *   **Resultado Esperado:** O botão volta para "Marcar Presença" e o evento some da lista no seu painel.

### **Onde Eu Parei:**



> * Começando os testes iniciais.
---