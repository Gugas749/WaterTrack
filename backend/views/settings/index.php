<?php
$this->title = 'Perfil';
?>

<div class="container my-5">
    <div class="row g-4">

        <!-- COLUNA ESQUERDA -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 p-4">

                <h5 class="mb-4 fw-semibold">Minhas Informações</h5>

                <div class="text-center mb-4">
                    <div class="profile-avatar mx-auto">
                        <i class="bi bi-camera"></i>
                    </div>
                </div>

                <form>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cliente ID</label>
                            <input type="text" class="form-control" value="#876370" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" class="form-control" value="2021-12-01">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" value="Alison G.">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="example@gmail.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Morada</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" value="Street">
                            <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3 text-primary"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-primary">
                            Resetar Password
                        </button>

                        <button type="button" class="btn btn-primary px-4">
                            Editar
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- COLUNA DIREITA -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 p-4 h-100">

                <h5 class="mb-4 fw-semibold">Configurações</h5>

                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg">
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg">
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg">
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg">
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg">
                </div>

            </div>
        </div>

    </div>
</div>
