
<?php
echo "<h2>🗄️ Administrador do Banco de Dados SQLite</h2>";

try {
    $dbFile = __DIR__ . '/../database.sqlite';
    
    if (!file_exists($dbFile)) {
        echo "<p style='color: red;'>❌ Arquivo database.sqlite não encontrado!</p>";
        exit;
    }
    
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .table-name { background-color: #e8f5e8; padding: 10px; margin: 20px 0; }
        .nav { margin: 20px 0; }
        .nav a { margin-right: 15px; text-decoration: none; color: #333; padding: 8px 15px; background: #f0f0f0; border-radius: 4px; }
        .nav a:hover { background: #ddd; }
        .nav a.active { background: #007cba; color: white; }
        .form-container { background: #f9f9f9; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .form-row { margin: 10px 0; }
        .form-row label { display: inline-block; width: 120px; font-weight: bold; }
        .form-row input, .form-row textarea, .form-row select { width: 300px; padding: 5px; }
        .btn { padding: 8px 15px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #005a8b; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .alert { padding: 15px; margin: 15px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .editable { cursor: pointer; }
        .editable:hover { background: #fff3cd; }
        .sql-editor { width: 100%; height: 200px; font-family: monospace; }
        .mass-actions { background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px; border: 1px solid #ffeaa7; }
        .checkbox-column { width: 40px; text-align: center; }
        #selectAll { cursor: pointer; }
    </style>";
    
    echo "<div class='container'>";
    
    // Navegação
    echo "<div class='nav'>";
    echo "<a href='?action=tables'>📋 Tabelas</a>";
    echo "<a href='?action=usuarios'>👤 Usuários</a>";
    echo "<a href='?action=mensagens'>💬 Mensagens</a>";
    echo "<a href='?action=news'>📰 Notícias</a>";
    echo "<a href='?action=sql'>⚡ SQL Direto</a>";
    echo "<a href='?action=create_table'>➕ Criar Tabela</a>";
    echo "<a href='../index.php'>🏠 Voltar ao Jogo</a>";
    echo "</div>";
    
    $action = $_GET['action'] ?? 'tables';
    $message = '';
    
    // Processar ações POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (isset($_POST['sql_execute'])) {
                $sql = trim($_POST['sql_query']);
                if (!empty($sql)) {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $message = "<div class='alert alert-success'>✅ SQL executado com sucesso!</div>";
                }
            } elseif (isset($_POST['create_table'])) {
                $tableName = $_POST['table_name'];
                $columns = $_POST['columns'];
                
                $sql = "CREATE TABLE `$tableName` ($columns)";
                $pdo->exec($sql);
                $message = "<div class='alert alert-success'>✅ Tabela '$tableName' criada com sucesso!</div>";
            } elseif (isset($_POST['drop_table'])) {
                $tableName = $_POST['table_name'];
                $pdo->exec("DROP TABLE `$tableName`");
                $message = "<div class='alert alert-success'>✅ Tabela '$tableName' removida com sucesso!</div>";
            } elseif (isset($_POST['update_record'])) {
                $table = $_POST['table'];
                $id = $_POST['id'];
                $updates = [];
                
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'field_') === 0) {
                        $fieldName = substr($key, 6);
                        $updates[] = "`$fieldName` = " . $pdo->quote($value);
                    }
                }
                
                if (!empty($updates)) {
                    $sql = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id]);
                    $message = "<div class='alert alert-success'>✅ Registro atualizado com sucesso!</div>";
                }
            } elseif (isset($_POST['delete_record'])) {
                $table = $_POST['table'];
                $id = $_POST['id'];
                
                $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
                $stmt->execute([$id]);
                $message = "<div class='alert alert-success'>✅ Registro removido com sucesso!</div>";
            } elseif (isset($_POST['mass_delete'])) {
                $table = $_POST['table'];
                $selectedIds = $_POST['selected_ids'] ?? [];
                
                if (!empty($selectedIds)) {
                    $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
                    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id IN ($placeholders)");
                    $stmt->execute($selectedIds);
                    $count = count($selectedIds);
                    $message = "<div class='alert alert-success'>✅ $count registros removidos com sucesso!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>❌ Nenhum registro selecionado!</div>";
                }
            } elseif (isset($_POST['truncate_table'])) {
                $table = $_POST['table'];
                $pdo->exec("DELETE FROM `$table`");
                $pdo->exec("DELETE FROM sqlite_sequence WHERE name='$table'");
                $message = "<div class='alert alert-success'>✅ Tabela '$table' esvaziada completamente!</div>";
            } elseif (isset($_POST['insert_record'])) {
                $table = $_POST['table'];
                $fields = [];
                $values = [];
                
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'new_') === 0) {
                        $fieldName = substr($key, 4);
                        if (!empty($value)) {
                            $fields[] = "`$fieldName`";
                            $values[] = $pdo->quote($value);
                        }
                    }
                }
                
                if (!empty($fields)) {
                    $sql = "INSERT INTO `$table` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
                    $pdo->exec($sql);
                    $message = "<div class='alert alert-success'>✅ Novo registro inserido com sucesso!</div>";
                }
            }
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    // Exibir mensagem se houver
    if ($message) {
        echo $message;
    }
    
    switch($action) {
        case 'tables':
            echo "<h3>📋 Gerenciar Tabelas</h3>";
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
            $tables = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>Nome da Tabela</th><th>Registros</th><th>Ações</th></tr>";
            
            foreach($tables as $table) {
                $tableName = $table['name'];
                $count = $pdo->query("SELECT COUNT(*) FROM `$tableName`")->fetchColumn();
                
                echo "<tr>";
                echo "<td><strong>$tableName</strong></td>";
                echo "<td>$count registros</td>";
                echo "<td>";
                echo "<a href='?action=view&table=$tableName' class='btn'>👁️ Ver Dados</a> ";
                echo "<a href='?action=edit_table&table=$tableName' class='btn'>✏️ Editar</a> ";
                echo "<a href='?action=structure&table=$tableName' class='btn'>🏗️ Estrutura</a> ";
                echo "<form style='display:inline;' method='post' onsubmit='return confirm(\"Tem certeza que deseja remover a tabela $tableName?\");'>";
                echo "<input type='hidden' name='table_name' value='$tableName'>";
                echo "<button type='submit' name='drop_table' class='btn btn-danger'>🗑️ Remover</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
            break;
            
        case 'view':
        case 'edit_table':
            $table = $_GET['table'] ?? '';
            if($table) {
                $isEdit = ($action === 'edit_table');
                echo "<h3>" . ($isEdit ? "✏️ Editar" : "👁️ Ver") . " Dados da Tabela: $table</h3>";
                
                // Contar registros
                $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                echo "<p><strong>Total de registros:</strong> $count</p>";
                
                if ($isEdit) {
                    // Ações em massa
                    // Verificar se a tabela tem coluna id
                    $stmt = $pdo->query("PRAGMA table_info(`$table`)");
                    $columns = $stmt->fetchAll();
                    $hasIdColumn = false;
                    foreach($columns as $column) {
                        if ($column['name'] === 'id') {
                            $hasIdColumn = true;
                            break;
                        }
                    }
                    
                    echo "<div class='mass-actions'>";
                    echo "<h4>🔧 Ações em Massa</h4>";
                    
                    if ($hasIdColumn) {
                        echo "<form method='post' style='display: inline;' onsubmit='return confirmMassDelete();'>";
                        echo "<input type='hidden' name='table' value='$table'>";
                        echo "<button type='submit' name='mass_delete' class='btn btn-danger'>🗑️ Deletar Selecionados</button>";
                        echo "<input type='hidden' id='selected_ids_input' name='selected_ids[]' value=''>";
                        echo "</form> ";
                    } else {
                        echo "<em>⚠️ Tabela sem coluna ID - deleção em massa não disponível</em><br>";
                    }
                    
                    echo "<form method='post' style='display: inline;' onsubmit='return confirm(\"ATENÇÃO: Isso irá apagar TODOS os registros da tabela $table! Tem certeza?\");'>";
                    echo "<input type='hidden' name='table' value='$table'>";
                    echo "<button type='submit' name='truncate_table' class='btn btn-warning'>⚠️ Esvaziar Tabela</button>";
                    echo "</form>";
                    echo "</div>";
                    
                    // Formulário para inserir novo registro
                    echo "<div class='form-container'>";
                    echo "<h4>➕ Inserir Novo Registro</h4>";
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='table' value='$table'>";
                    
                    $stmt = $pdo->query("PRAGMA table_info(`$table`)");
                    $columns = $stmt->fetchAll();
                    
                    foreach($columns as $column) {
                        if ($column['name'] !== 'id') {
                            echo "<div class='form-row'>";
                            echo "<label>{$column['name']}:</label>";
                            if (strpos($column['type'], 'TEXT') !== false) {
                                echo "<textarea name='new_{$column['name']}' placeholder='{$column['name']}'></textarea>";
                            } else {
                                echo "<input type='text' name='new_{$column['name']}' placeholder='{$column['name']}'>";
                            }
                            echo "</div>";
                        }
                    }
                    
                    echo "<button type='submit' name='insert_record' class='btn btn-success'>➕ Inserir</button>";
                    echo "</form>";
                    echo "</div>";
                }
                
                if($count > 0) {
                    $limit = $_GET['limit'] ?? 20;
                    $offset = $_GET['offset'] ?? 0;
                    
                    $stmt = $pdo->query("SELECT * FROM `$table` LIMIT $limit OFFSET $offset");
                    $data = $stmt->fetchAll();
                    
                    if($data) {
                        echo "<form id='massDeleteForm'>";
                        echo "<table>";
                        // Cabeçalho
                        echo "<tr>";
                        if ($isEdit) {
                            echo "<th class='checkbox-column'>";
                            if ($hasIdColumn) {
                                echo "<input type='checkbox' id='selectAll' title='Selecionar todos'>";
                            } else {
                                echo "Sel.";
                            }
                            echo "</th>";
                        }
                        foreach(array_keys($data[0]) as $column) {
                            echo "<th>$column</th>";
                        }
                        if ($isEdit) {
                            echo "<th>Ações</th>";
                        }
                        echo "</tr>";
                        
                        // Verificar se a tabela tem coluna id
                        $hasIdColumn = array_key_exists('id', $data[0]);
                        
                        // Dados
                        foreach($data as $row) {
                            echo "<tr>";
                            if ($isEdit && $hasIdColumn) {
                                echo "<td class='checkbox-column'><input type='checkbox' class='record-checkbox' value='{$row['id']}'></td>";
                            } elseif ($isEdit && !$hasIdColumn) {
                                echo "<td class='checkbox-column'>-</td>";
                            }
                            
                            foreach($row as $key => $value) {
                                if ($isEdit && $key !== 'id') {
                                    echo "<td>";
                                    if ($hasIdColumn) {
                                        echo "<form method='post' style='margin:0;'>";
                                        echo "<input type='hidden' name='table' value='$table'>";
                                        echo "<input type='hidden' name='id' value='{$row['id']}'>";
                                        if ($value !== null && strlen($value) > 100) {
                                            echo "<textarea name='field_$key' style='width:200px;height:50px;'>" . htmlspecialchars($value ?? '') . "</textarea>";
                                        } else {
                                            echo "<input type='text' name='field_$key' value='" . htmlspecialchars($value ?? '') . "' style='width:150px;'>";
                                        }
                                        echo "</form>";
                                    } else {
                                        // Tabela sem ID - mostrar apenas como texto
                                        $displayValue = ($value === null) ? '<em>NULL</em>' : htmlspecialchars(substr($value ?? '', 0, 150));
                                        echo "<span title='Tabela sem coluna ID - não editável'>$displayValue</span>";
                                    }
                                    echo "</td>";
                                } else {
                                    $displayValue = ($value === null) ? '<em>NULL</em>' : htmlspecialchars(substr($value ?? '', 0, 100));
                                    echo "<td>$displayValue</td>";
                                }
                            }
                            
                            if ($isEdit) {
                                echo "<td>";
                                if ($hasIdColumn) {
                                    echo "<button onclick='updateRecord(this)' class='btn btn-success'>💾 Salvar</button> ";
                                    echo "<form style='display:inline;' method='post' onsubmit='return confirm(\"Confirma remoção?\");'>";
                                    echo "<input type='hidden' name='table' value='$table'>";
                                    echo "<input type='hidden' name='id' value='{$row['id']}'>";
                                    echo "<button type='submit' name='delete_record' class='btn btn-danger'>🗑️</button>";
                                    echo "</form>";
                                } else {
                                    echo "<em>Sem ID</em>";
                                }
                                echo "</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table>";
                        echo "</form>";
                        
                        // Paginação
                        if($count > $limit) {
                            echo "<div class='nav'>";
                            if($offset > 0) {
                                $prevOffset = max(0, $offset - $limit);
                                echo "<a href='?action=$action&table=$table&offset=$prevOffset&limit=$limit'>« Anterior</a>";
                            }
                            if($offset + $limit < $count) {
                                $nextOffset = $offset + $limit;
                                echo "<a href='?action=$action&table=$table&offset=$nextOffset&limit=$limit'>Próximo »</a>";
                            }
                            echo "</div>";
                        }
                    }
                } else {
                    echo "<p>Tabela vazia.</p>";
                }
                
                if ($isEdit) {
                    echo "<script>
                    function updateRecord(btn) {
                        const row = btn.closest('tr');
                        const forms = row.querySelectorAll('form');
                        if (forms.length > 0) {
                            const form = forms[0];
                            const hiddenUpdate = document.createElement('input');
                            hiddenUpdate.type = 'hidden';
                            hiddenUpdate.name = 'update_record';
                            hiddenUpdate.value = '1';
                            form.appendChild(hiddenUpdate);
                            form.submit();
                        }
                    }
                    
                    // Funcionalidade de seleção em massa (apenas se houver coluna ID)
                    const selectAllEl = document.getElementById('selectAll');
                    if (selectAllEl) {
                        selectAllEl.addEventListener('change', function() {
                            const checkboxes = document.querySelectorAll('.record-checkbox');
                            checkboxes.forEach(checkbox => {
                                checkbox.checked = this.checked;
                            });
                        });
                    }
                    
                    function confirmMassDelete() {
                        const selected = Array.from(document.querySelectorAll('.record-checkbox:checked')).map(cb => cb.value);
                        if (selected.length === 0) {
                            alert('Selecione pelo menos um registro para deletar.');
                            return false;
                        }
                        
                        // Adicionar IDs selecionados ao formulário
                        const form = event.target.closest('form');
                        const input = form.querySelector('#selected_ids_input');
                        
                        // Limpar inputs existentes
                        form.querySelectorAll('input[name=\"selected_ids[]\"]').forEach(input => input.remove());
                        
                        // Adicionar novos inputs
                        selected.forEach(id => {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'selected_ids[]';
                            hiddenInput.value = id;
                            form.appendChild(hiddenInput);
                        });
                        
                        return confirm('Tem certeza que deseja deletar ' + selected.length + ' registros selecionados?');
                    }
                    </script>";
                }
            }
            break;
            
        case 'structure':
            $table = $_GET['table'] ?? '';
            if($table) {
                echo "<h3>🏗️ Estrutura da Tabela: $table</h3>";
                
                $stmt = $pdo->query("PRAGMA table_info(`$table`)");
                $columns = $stmt->fetchAll();
                
                echo "<table>";
                echo "<tr><th>Coluna</th><th>Tipo</th><th>Nulo</th><th>Padrão</th><th>Chave Primária</th></tr>";
                
                foreach($columns as $column) {
                    echo "<tr>";
                    echo "<td><strong>" . $column['name'] . "</strong></td>";
                    echo "<td>" . $column['type'] . "</td>";
                    echo "<td>" . ($column['notnull'] ? 'NÃO' : 'SIM') . "</td>";
                    echo "<td>" . ($column['dflt_value'] ?? '<em>Nenhum</em>') . "</td>";
                    echo "<td>" . ($column['pk'] ? 'SIM' : 'NÃO') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            break;
            
        case 'sql':
            echo "<h3>⚡ Executar SQL Diretamente</h3>";
            echo "<div class='alert alert-info'>⚠️ Cuidado! Execute apenas comandos SQL que você entende completamente.</div>";
            
            echo "<form method='post'>";
            echo "<div class='form-row'>";
            echo "<label>Comando SQL:</label><br>";
            echo "<textarea name='sql_query' class='sql-editor' placeholder='Digite seu comando SQL aqui...'>" . (isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : '') . "</textarea>";
            echo "</div>";
            echo "<button type='submit' name='sql_execute' class='btn'>⚡ Executar SQL</button>";
            echo "</form>";
            
            if (isset($_POST['sql_execute']) && !empty($_POST['sql_query'])) {
                try {
                    $sql = trim($_POST['sql_query']);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    
                    if (stripos($sql, 'SELECT') === 0) {
                        $results = $stmt->fetchAll();
                        if ($results) {
                            echo "<h4>📊 Resultados:</h4>";
                            echo "<table>";
                            echo "<tr>";
                            foreach(array_keys($results[0]) as $column) {
                                echo "<th>$column</th>";
                            }
                            echo "</tr>";
                            foreach($results as $row) {
                                echo "<tr>";
                                foreach($row as $value) {
                                    echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
                                }
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p>Nenhum resultado encontrado.</p>";
                        }
                    }
                } catch (Exception $e) {
                    // Erro já exibido acima
                }
            }
            break;
            
        case 'create_table':
            echo "<h3>➕ Criar Nova Tabela</h3>";
            
            echo "<form method='post'>";
            echo "<div class='form-row'>";
            echo "<label>Nome da Tabela:</label>";
            echo "<input type='text' name='table_name' required>";
            echo "</div>";
            echo "<div class='form-row'>";
            echo "<label>Colunas (SQL):</label>";
            echo "<textarea name='columns' required placeholder='id INTEGER PRIMARY KEY AUTOINCREMENT,
nome TEXT NOT NULL,
email TEXT,
data DATETIME DEFAULT CURRENT_TIMESTAMP'></textarea>";
            echo "</div>";
            echo "<button type='submit' name='create_table' class='btn btn-success'>➕ Criar Tabela</button>";
            echo "</form>";
            
            echo "<div class='alert alert-info'>";
            echo "<h4>💡 Exemplo de estrutura:</h4>";
            echo "<pre>id INTEGER PRIMARY KEY AUTOINCREMENT,
nome TEXT NOT NULL,
email TEXT UNIQUE,
idade INTEGER,
data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP</pre>";
            echo "</div>";
            break;
            
        case 'usuarios':
            echo "<h3>👤 Usuários do Sistema</h3>";
            $stmt = $pdo->query("SELECT id, usuario, email, nivel, yens, energia, status, reg FROM usuarios ORDER BY id LIMIT 50");
            $usuarios = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Usuário</th><th>Email</th><th>Nível</th><th>Yens</th><th>Energia</th><th>Status</th><th>Registro</th></tr>";
            
            foreach($usuarios as $user) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td><strong>" . htmlspecialchars($user['usuario'] ?? '') . "</strong></td>";
                echo "<td>" . htmlspecialchars($user['email'] ?? '') . "</td>";
                echo "<td>" . $user['nivel'] . "</td>";
                echo "<td>" . $user['yens'] . "</td>";
                echo "<td>" . $user['energia'] . "</td>";
                echo "<td>" . $user['status'] . "</td>";
                echo "<td>" . $user['reg'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            break;
            
        case 'mensagens':
            echo "<h3>💬 Mensagens do Sistema</h3>";
            $stmt = $pdo->query("SELECT m.*, u1.usuario as remetente, u2.usuario as destinatario 
                                FROM mensagens m 
                                LEFT JOIN usuarios u1 ON m.origem = u1.id 
                                LEFT JOIN usuarios u2 ON m.destino = u2.id 
                                ORDER BY m.data DESC LIMIT 30");
            $mensagens = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID</th><th>De</th><th>Para</th><th>Assunto</th><th>Data</th><th>Status</th></tr>";
            
            foreach($mensagens as $msg) {
                echo "<tr>";
                echo "<td>" . $msg['id'] . "</td>";
                echo "<td>" . htmlspecialchars($msg['remetente'] ?? 'Sistema') . "</td>";
                echo "<td>" . htmlspecialchars($msg['destinatario'] ?? 'Desconhecido') . "</td>";
                echo "<td>" . htmlspecialchars(substr($msg['assunto'] ?? '', 0, 50)) . "</td>";
                echo "<td>" . $msg['data'] . "</td>";
                echo "<td>" . $msg['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            break;
            
        case 'news':
            echo "<h3>📰 Notícias do Sistema</h3>";
            $stmt = $pdo->query("SELECT * FROM news ORDER BY data DESC LIMIT 20");
            $news = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Assunto</th><th>Autor</th><th>Data</th></tr>";
            
            foreach($news as $noticia) {
                echo "<tr>";
                echo "<td>" . $noticia['id'] . "</td>";
                echo "<td>" . htmlspecialchars(substr($noticia['assunto'] ?? '', 0, 50)) . "</td>";
                echo "<td>" . htmlspecialchars($noticia['autor'] ?? '') . "</td>";
                echo "<td>" . $noticia['data'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            break;
    }
    
    echo "</div>"; // container
    echo "<hr>";
    echo "<p><small>📍 Arquivo: $dbFile | Tamanho: " . number_format(filesize($dbFile) / 1024, 2) . " KB</small></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro ao conectar ao banco: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro geral: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
