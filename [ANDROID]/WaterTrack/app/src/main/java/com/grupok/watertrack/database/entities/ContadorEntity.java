package com.grupok.watertrack.database.entities;

import androidx.room.ColumnInfo;
import androidx.room.Entity;
import androidx.room.PrimaryKey;

@Entity(tableName = "Contadores")
public class ContadorEntity {

    @PrimaryKey(autoGenerate = true)
    public long id;

    @ColumnInfo(name = "nome")
    public String nome;

    @ColumnInfo(name = "morada")
    public String morada;

    @ColumnInfo(name = "id_morador")
    public Long idMorador;

    @ColumnInfo(name = "id_tipo")
    public Long idTipo;

    @ColumnInfo(name = "id_empresa")
    public Long idEmpresa;

    @ColumnInfo(name = "classe")
    public String classe;

    @ColumnInfo(name = "data_instalacao")
    public String dataInstalacao;

    @ColumnInfo(name = "capacidade_max")
    public String capacidadeMax;

    @ColumnInfo(name = "unidade_medida")
    public String unidadeMedida;

    @ColumnInfo(name = "temperatura_suportada")
    public String temperaturaSuportada;

    @ColumnInfo(name = "estado")
    public int estado;

    public ContadorEntity(String nome, String morada, Long idMorador, Long idTipo, Long idEmpresa,
                          String classe, String dataInstalacao, String capacidadeMax,
                          String unidadeMedida, String temperaturaSuportada, int estado) {
        this.nome = nome;
        this.morada = morada;
        this.idMorador = idMorador;
        this.idTipo = idTipo;
        this.idEmpresa = idEmpresa;
        this.classe = classe;
        this.dataInstalacao = dataInstalacao;
        this.capacidadeMax = capacidadeMax;
        this.unidadeMedida = unidadeMedida;
        this.temperaturaSuportada = temperaturaSuportada;
        this.estado = estado;
    }
}
