# Sistema de Gestão de Agendamentos (SGA)

Sistema web para gerenciamento de agendamentos da quadra esportiva, com funcionalidades específicas para diferentes tipos de usuários.

## 📋 Funcionalidades Implementadas

### 🔐 Sistema de Autenticação
- Login e logout de usuários
- Registro com validação de email institucional (@unifio.edu.br e @fio.edu.br)
- Recuperação de senha via email (simulado em ambiente de teste)
- Diferentes níveis de acesso (Super Admin, Admin Atlética, Usuários)

### 👥 Tipos de Usuário
- **Aluno**: Estudantes da instituição
- **Membro das Atléticas**: Estudantes membros de atléticas
- **Professor**: Professores da instituição
- **Comunidade Externa**: Usuários externos
- **Admin Atlética**: Administradores das atléticas
- **Super Admin**: Administrador geral do sistema

### 📅 Sistema de Agendamentos
- **Agendamento de Horário na Quadra** com calendário visual
- Validação de conflitos de horário (impede agendamentos duplicados)
- Campo obrigatório de "quantidade de pessoas aproximadas"
- Diferentes tipos: Esportivo e Não Esportivo
- Aprovação por super admin
- Períodos fixos: 19:15-20:55 e 21:10-22:50

### 🏃‍♂️ Gestão de Modalidades Esportivas
- Cadastro e edição de modalidades
- Inscrições de alunos em modalidades
- Aprovação de inscrições por admins das atléticas

### 🏆 Sistema de Atléticas
- Gestão de atléticas e cursos vinculados
- Solicitações de entrada em atléticas
- Administradores específicos por atlética

### ✅ Sistema de Presenças
- Marcação individual de presença em eventos
- **Confirmação de presença da atlética** com controle de quantidade
- Controle de quantidade de pessoas por atlética

### 📊 Relatórios Avançados (Super Admin)
- **Relatório de evento específico** com lista completa de presenças
- **Relatório geral** com estatísticas detalhadas do período
- Dados por modalidade e por atlética
- Função de impressão otimizada

### 📈 Dashboard Personalizado
- **Dashboard do Admin da Atlética** com painéis coloridos organizados
- **Dashboard do Super Admin** com acesso a todas as funcionalidades
- **Dashboard do Usuário** com informações personalizadas por tipo

## 🛠 Tecnologias Utilizadas
- PHP 8.x
- MySQL/MariaDB
- Bootstrap 5
- JavaScript
- HTML5/CSS3

## 📦 Instalação

### Pré-requisitos
- XAMPP ou servidor similar
- PHP 8.0+
- MySQL/MariaDB

### Passos de Instalação
1. Clone o projeto na pasta htdocs do XAMPP:
   ```bash
   git clone https://github.com/rafaeldiasgarcia/sga-unifio.git C:\xampp\htdocs\sga
   ```

2. Importe o banco de dados:
   - Acesse phpMyAdmin
   - Crie um banco chamado `sga_db`
   - Importe o arquivo `databases/sga_db.sql`

3. Execute as atualizações do banco:
   - Execute o script `database_updates.sql` no phpMyAdmin

4. Configure o arquivo `config.php` se necessário

5. Acesse o sistema em: `http://localhost/sga`

## 🔧 Configuração

### Banco de Dados
O arquivo `config.php` contém as configurações de conexão com o banco de dados.

### Email (Recuperação de Senha)
Configure as credenciais SMTP no arquivo `config.php` para o funcionamento da recuperação de senha.

## 📋 CHECKLIST COMPLETO DE TESTES

### ✅ 1. AUTENTICAÇÃO E REGISTRO

#### 1.1 Registro de Usuários
- [ ] **Aluno com email @unifio.edu.br**
  - Preencher nome, RA (6 dígitos), curso, email @unifio.edu.br, senha
  - Verificar se o registro é aceito
  - Verificar se automaticamente solicita entrada na atlética (se o curso tiver)

- [ ] **Aluno com email @fio.edu.br**
  - Mesmo processo acima com email @fio.edu.br
  - Verificar funcionamento idêntico

- [ ] **Professor com múltiplos cursos**
  - Preencher dados sem RA
  - Selecionar múltiplos cursos
  - Verificar permissões de agendamento

- [ ] **Membro das Atléticas**
  - Registrar como membro direto
  - Verificar status "aprovado" automático se o curso tiver atlética

- [ ] **Comunidade Externa**
  - Usar qualquer email (gmail, yahoo, etc.)
  - Verificar que não precisa de email institucional

- [ ] **Validações de Erro**
  - Tentar email inválido para aluno (deve falhar)
  - RA com menos/mais de 6 dígitos (deve falhar)
  - Senhas não coincidentes (deve falhar)
  - Campos obrigatórios vazios (deve falhar)

#### 1.2 Login e Logout
- [ ] **Login com credenciais válidas**
  - Testar com cada tipo de usuário
  - Verificar redirecionamento correto para dashboard específico

- [ ] **Login com credenciais inválidas**
  - Email inexistente (deve mostrar erro)
  - Senha incorreta (deve mostrar erro)

- [ ] **Logout do sistema**
  - Clicar em logout
  - Verificar redirecionamento para página de login
  - Tentar acessar área restrita (deve redirecionar para login)

#### 1.3 Recuperação de Senha
- [ ] **Processo de recuperação**
  - Inserir email válido cadastrado
  - Copiar link gerado (ambiente de teste)
  - Acessar link e redefinir senha
  - Fazer login com nova senha

### ✅ 2. SISTEMA DE AGENDAMENTOS

#### 2.1 Criação de Agendamentos
- [ ] **Acesso à página de agendamento**
  - Login como professor, super admin ou admin atlética
  - Verificar acesso ao menu "Agendar Evento"
  - Verificar calendário na lateral direita

- [ ] **Agendamento Esportivo**
  - Preencher título do evento
  - Selecionar tipo "Esportivo"
  - Escolher modalidade esportiva
  - Definir data e período
  - Informar quantidade de pessoas (obrigatório)
  - Adicionar descrição
  - Submeter formulário

- [ ] **Agendamento Não Esportivo**
  - Mesmo processo, mas tipo "Não Esportivo"
  - Verificar que não pede modalidade

- [ ] **Campo Quantidade de Pessoas**
  - Tentar submeter com quantidade 0 (deve dar erro)
  - Tentar submeter sem preencher (deve dar erro)
  - Preencher com número válido (deve aceitar)

#### 2.2 Validação de Conflitos
- [ ] **Teste de conflito de horários**
  - Agendar evento para data/período específico
  - Como super admin, aprovar o evento
  - Tentar agendar outro evento para mesmo horário
  - Verificar mensagem de erro clara

- [ ] **Calendário Visual**
  - Verificar eventos aprovados aparecem no calendário
  - Navegar entre meses
  - Verificar cores/indicações visuais

#### 2.3 Aprovação de Agendamentos (Super Admin)
- [ ] **Gerenciar agendamentos pendentes**
  - Login como super admin
  - Acessar "Aprovar Agendamentos"
  - Visualizar lista de pendentes
  - Aprovar agendamento
  - Rejeitar agendamento
  - Verificar mudança de status

### ✅ 3. DASHBOARD E NAVEGAÇÃO

#### 3.1 Dashboard do Super Admin
- [ ] **Acesso completo**
  - Verificar todos os cards de gerenciamento
  - Testar acesso a cada funcionalidade
  - Verificar calendário de eventos

#### 3.2 Dashboard do Admin Atlética
- [ ] **Painéis organizados por cor**
  - **Painel Amarelo**: Eventos confirmados pela atlética
  - **Painel Verde**: Atletas aprovados/gerenciar equipes
  - **Painel Azul**: Solicitações de entrada na atlética
  - Verificar contadores dinâmicos em cada painel

#### 3.3 Dashboard do Usuário
- [ ] **Personalização por tipo**
  - Professor: ver agendamentos criados
  - Aluno: ver atividades disponíveis
  - Verificar seção de presenças marcadas

### ✅ 4. SISTEMA DE ATLÉTICAS

#### 4.1 Gestão de Membros (Admin Atlética)
- [ ] **Aceitar solicitações**
  - Login como admin atlética
  - Acessar "Aceitar Solicitações"
  - Aprovar aluno pendente
  - Verificar mudança de status para "aprovado"

- [ ] **Remover membros**
  - Acessar lista de membros aprovados
  - Remover membro da atlética
  - Verificar confirmação antes da remoção

#### 4.2 Solicitação de Entrada (Aluno)
- [ ] **Processo de solicitação**
  - Login como aluno não membro
  - Acessar área de atléticas
  - Solicitar entrada na atlética
  - Verificar status "pendente"

### ✅ 5. MODALIDADES ESPORTIVAS

#### 5.1 Inscrições em Modalidades (Aluno)
- [ ] **Processo de inscrição**
  - Login como aluno membro de atlética
  - Acessar "Inscrever em Modalidades"
  - Escolher modalidade disponível
  - Confirmar inscrição
  - Verificar status "pendente"

#### 5.2 Aprovação de Inscrições (Admin Atlética)
- [ ] **Gerenciar inscrições**
  - Login como admin atlética
  - Acessar "Gerenciar Inscrições"
  - Ver lista de pendentes
  - Aprovar inscrição
  - Verificar mudança para "aprovado"

- [ ] **Remover atletas**
  - Ver lista de aprovados
  - Remover atleta de modalidade
  - Verificar funcionamento do botão

### ✅ 6. SISTEMA DE PRESENÇAS

#### 6.1 Presença Individual
- [ ] **Marcar presença**
  - Login como usuário qualquer
  - Acessar "Agenda da Quadra"
  - Marcar presença em evento aprovado
  - Verificar badge de confirmação

- [ ] **Desmarcar presença**
  - Desmarcar presença anteriormente marcada
  - Verificar remoção da confirmação

#### 6.2 Confirmação de Presença da Atlética (Admin Atlética)
- [ ] **Confirmar atlética**
  - Login como admin atlética
  - Acessar agenda de eventos
  - Clicar "Confirmar Atlética" em evento esportivo
  - Informar quantidade de pessoas da atlética
  - Verificar badge de confirmação exibido

- [ ] **Desconfirmar atlética**
  - Desconfirmar presença da atlética
  - Verificar remoção da confirmação

- [ ] **Persistência de dados**
  - Confirmar presença com quantidade específica
  - Sair e entrar novamente
  - Verificar se dados foram mantidos

### ✅ 7. RELATÓRIOS (Super Admin)

#### 7.1 Relatório de Evento Específico
- [ ] **Selecionar evento**
  - Login como super admin
  - Acessar "Relatórios"
  - Escolher "Relatório de Evento Específico"
  - Selecionar evento da lista

- [ ] **Dados do relatório**
  - Verificar informações do evento
  - Ver lista completa de presenças
  - Verificar dados de atlética confirmada
  - Testar função "Imprimir"

#### 7.2 Relatório Geral
- [ ] **Definir período**
  - Escolher "Relatório Geral"
  - Definir data início e fim
  - Gerar relatório

- [ ] **Estatísticas apresentadas**
  - Total de eventos por status
  - Eventos esportivos vs não esportivos
  - Eventos com atlética confirmada
  - Total de pessoas estimadas
  - Dados por modalidade
  - Dados por atlética
  - Lista detalhada de eventos

- [ ] **Função de impressão**
  - Testar botão imprimir
  - Verificar formatação para impressão

### ✅ 8. GESTÃO DE ESTRUTURA (Super Admin)

#### 8.1 Gerenciamento de Atléticas
- [ ] **CRUD de atléticas**
  - Criar nova atlética
  - Editar atlética existente
  - Visualizar lista de atléticas

#### 8.2 Gerenciamento de Cursos
- [ ] **CRUD de cursos**
  - Criar novo curso
  - Associar curso a atlética
  - Editar curso existente

#### 8.3 Gerenciamento de Admins
- [ ] **Promover usuários**
  - Visualizar lista de usuários
  - Promover aluno a admin de atlética
  - Verificar permissões do novo admin

#### 8.4 Gerenciamento de Modalidades
- [ ] **CRUD de modalidades**
  - Criar nova modalidade esportiva
  - Associar a evento ativo
  - Editar modalidade existente

### ✅ 9. VALIDAÇÕES E SEGURANÇA

#### 9.1 Validação de Email por Tipo
- [ ] **Restrições corretas**
  - Aluno: apenas @unifio.edu.br e @fio.edu.br
  - Professor: apenas emails institucionais
  - Membro Atlética: apenas emails institucionais
  - Comunidade Externa: qualquer email

#### 9.2 Controle de Acesso
- [ ] **Permissões por role**
  - Usuário comum: acesso limitado
  - Admin atlética: acesso a sua atlética apenas
  - Super admin: acesso total

- [ ] **Tentativas de acesso indevido**
  - Tentar acessar área de super admin sem permissão
  - Tentar acessar área de admin atlética de outra atlética
  - Verificar redirecionamentos de segurança

### ✅ 10. FUNCIONALIDADES ESPECIAIS

#### 10.1 Calendário Interativo
- [ ] **Navegação**
  - Navegar entre meses
  - Verificar eventos exibidos corretamente
  - Testar em página de agendamento

#### 10.2 Interface Responsiva
- [ ] **Dispositivos móveis**
  - Testar em smartphone
  - Verificar menus e botões
  - Testar formulários

#### 10.3 Badges e Indicadores Visuais
- [ ] **Status visual**
  - Badges de status em agendamentos
  - Indicadores de presença confirmada
  - Cores específicas nos painéis

### ✅ 11. TESTES DE INTEGRAÇÃO

#### 11.1 Fluxo Completo do Aluno
1. [ ] Registrar como aluno
2. [ ] Solicitar entrada na atlética
3. [ ] Admin aprova entrada
4. [ ] Inscrever-se em modalidade
5. [ ] Admin aprova inscrição
6. [ ] Marcar presença em evento
7. [ ] Verificar nos relatórios

#### 11.2 Fluxo Completo do Professor
1. [ ] Registrar como professor
2. [ ] Agendar evento esportivo
3. [ ] Super admin aprova evento
4. [ ] Alunos marcam presença
5. [ ] Gerar relatório do evento

#### 11.3 Fluxo Completo de Admin Atlética
1. [ ] Ser promovido a admin
2. [ ] Aceitar solicitações de entrada
3. [ ] Aprovar inscrições em modalidades
4. [ ] Confirmar presença da atlética em eventos
5. [ ] Gerenciar equipes

## 🚀 Novas Funcionalidades Implementadas

### 1. Calendário Visual Avançado
- Exibição de eventos aprovados com cores específicas
- Navegação fluida entre meses
- Integração em tempo real com agendamentos

### 2. Sistema de Validação de Conflitos
- Verificação automática de horários ocupados
- Mensagens de erro claras e específicas
- Prevenção de agendamentos duplicados

### 3. Confirmação de Presença da Atlética
- Modal interativo para inserir quantidade
- Badge visual de confirmação
- Controle completo por admin da atlética

### 4. Dashboard Reorganizado com Cores
- Painéis temáticos por funcionalidade
- Contadores dinâmicos em tempo real
- Links diretos para ações principais

### 5. Sistema de Relatórios Completo
- Relatórios específicos e gerais
- Estatísticas detalhadas por período
- Função de impressão otimizada

### 6. Validação Avançada de Campos
- Campo quantidade de pessoas obrigatório
- Validação de RA com 6 dígitos exatos
- Verificação de emails institucionais por tipo

## 📝 Observações de Desenvolvimento

### Estrutura do Banco de Dados
- Tabelas otimizadas para performance
- Relacionamentos bem definidos
- Campos para todas as funcionalidades

### Segurança Implementada
- Prepared statements em todas as queries
- Verificação de permissões em cada página
- Sanitização de dados de entrada

### Performance e UX
- Queries otimizadas com JOINs eficientes
- Interface responsiva e intuitiva
- Feedback visual imediato para ações

## 🐛 Debugging e Logs
Para ativação de logs detalhados durante desenvolvimento, configure o PHP para exibir erros.

## 📞 Suporte
Para dúvidas técnicas ou problemas de funcionamento, consulte a documentação do código ou entre em contato com a equipe de desenvolvimento.

---

**Versão**: 2.0  
**Última atualização**: Setembro 2025  
**Status**: Produção
