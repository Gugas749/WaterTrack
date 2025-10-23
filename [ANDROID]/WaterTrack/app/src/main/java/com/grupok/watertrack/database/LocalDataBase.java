package com.grupok.watertrack.database;

import android.content.Context;

import androidx.room.Database;
import androidx.room.Room;
import androidx.room.RoomDatabase;

import com.grupok.watertrack.database.daos.AvariasContadoresDao;
import com.grupok.watertrack.database.daos.ContadoresDao;
import com.grupok.watertrack.database.daos.EmpresasDao;
import com.grupok.watertrack.database.daos.LogsContadoresDao;
import com.grupok.watertrack.database.daos.TecnicoInfoDao;
import com.grupok.watertrack.database.daos.TiposContadoresDao;
import com.grupok.watertrack.database.daos.UserInfosDao;
import com.grupok.watertrack.database.entities.AvariasContadoresEntity;
import com.grupok.watertrack.database.entities.ContadorEntity;
import com.grupok.watertrack.database.entities.EmpresasEntity;
import com.grupok.watertrack.database.entities.LogsContadoresEntity;
import com.grupok.watertrack.database.entities.TecnicoInfoEntity;
import com.grupok.watertrack.database.entities.TiposContadoresEntity;
import com.grupok.watertrack.database.entities.UserInfosEntity;

@Database(entities = {UserInfosEntity.class, ContadorEntity.class, LogsContadoresEntity.class, AvariasContadoresEntity.class, EmpresasEntity.class, TecnicoInfoEntity.class, TiposContadoresEntity.class}, version = 2)
public abstract class LocalDataBase extends RoomDatabase {
    public abstract UserInfosDao userInfosDao();
    public abstract ContadoresDao contadoresDao();
    public abstract LogsContadoresDao logsContadoresDao();
    public abstract AvariasContadoresDao avariasContadoresDao();
    public abstract EmpresasDao empresasDao();
    public abstract TecnicoInfoDao tecnicoInfoDao();
    public abstract TiposContadoresDao tiposContadoresDao();
    public static LocalDataBase INSTANCE;
    public static LocalDataBase getInstance(Context context){
        if(INSTANCE==null)
        {
            INSTANCE= Room.databaseBuilder(context, LocalDataBase.class,"WaterTrackLocalDB")
                    .allowMainThreadQueries()
                    .fallbackToDestructiveMigration()
                    .build();

        }
        return INSTANCE;
    }
}
