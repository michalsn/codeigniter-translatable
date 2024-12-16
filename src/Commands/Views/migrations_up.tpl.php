$this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'null'           => false,
            ],

            /** Add your common fields */

            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('<?= $table; ?>');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'null'           => false,
            ],
            '<?= $tableForeignKey; ?>' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'null'       => false,
            ],

            /** Add your fields, which should have translated version */

        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['<?= $tableForeignKey; ?>', 'locale'], '<?= $table; ?>_locale');
        $this->forge->addForeignKey('<?= $tableForeignKey; ?>', '<?= $table; ?>', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('<?= $tableTranslations; ?>');
