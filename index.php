<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CINERAMA - Kiosko Autoservicio</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div id="app">
        <!-- Pantalla de Inicio -->
        <div id="screen-inicio" class="screen active">
            <div class="inicio-container">
                <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200&h=800&fit=crop" alt="Cine" class="inicio-bg">
                <div class="inicio-overlay">
                    <div class="logo">CINERAMA</div>
                    <h1>Bienvenido al Kiosko de Autoservicio</h1>
                    <button class="btn-primary btn-pulse" onclick="goToScreen('peliculas')">
                        <span>INICIAR COMPRA</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pantalla de Películas -->
        <div id="screen-peliculas" class="screen">
            <div class="header">
                <div class="logo-small">CINERAMA</div>
                <h2>Selecciona tu Película</h2>
            </div>
            <div class="content">
                <div id="peliculas-grid" class="peliculas-grid"></div>
            </div>
            <div class="footer">
                <button class="btn-secondary" onclick="goToScreen('inicio')">← Cancelar</button>
            </div>
        </div>

        <!-- Pantalla de Horarios -->
        <div id="screen-horarios" class="screen">
            <div class="header">
                <div class="logo-small">CINERAMA</div>
                <h2 id="pelicula-title"></h2>
            </div>
            <div class="content">
                <div id="horarios-container"></div>
            </div>
            <div class="footer">
                <button class="btn-secondary" onclick="goToScreen('peliculas')">← Atrás</button>
            </div>
        </div>

        <!-- Pantalla de Butacas -->
        <div id="screen-butacas" class="screen">
            <div class="header">
                <div class="logo-small">CINERAMA</div>
                <h2>Selecciona tus Butacas</h2>
                <div id="funcion-info" class="funcion-info"></div>
            </div>
            <div class="content">
                <div class="pantalla-cine">PANTALLA</div>
                <div id="mapa-butacas" class="mapa-butacas"></div>
                <div class="leyenda">
                    <div class="leyenda-item">
                        <div class="butaca-mini disponible" style="background: #4ade80;"></div>
                        <span>Disponible</span>
                    </div>
                    <div class="leyenda-item">
                        <div class="butaca-mini seleccionada" style="background: #dc2626; border: 2px solid #fff;"></div>
                        <span>Seleccionada</span>
                    </div>
                    <div class="leyenda-item">
                        <div class="butaca-mini ocupada" style="background: #6b7280;"></div>
                        <span>Ocupada</span>
                    </div>
                </div>
                <div class="butacas-seleccionadas">
                    <strong>Butacas seleccionadas:</strong> <span id="butacas-count">0</span>
                    <div id="butacas-list"></div>
                </div>
            </div>
            <div class="footer">
                <button class="btn-secondary" onclick="goToScreen('horarios')">← Atrás</button>
                <button class="btn-primary" onclick="goToScreen('tarifas')" id="btn-continuar-butacas" disabled>
                    Continuar →
                </button>
            </div>
        </div>

        <!-- Pantalla de Tarifas -->
        <div id="screen-tarifas" class="screen">
            <div class="header">
                <div class="logo-small">CINERAMA</div>
                <h2>Asigna Tarifas a las Butacas</h2>
            </div>
            <div class="content">
                <div class="tarifas-info">
                    <p>Debes asignar una tarifa a cada una de las <strong><span id="total-butacas">0</span></strong> butacas seleccionadas.</p>
                </div>
                <div id="tarifas-container" class="tarifas-grid"></div>
                <div class="tarifas-summary">
                    <div class="summary-row">
                        <span>Butacas asignadas:</span>
                        <span id="butacas-asignadas">0</span> / <span id="butacas-total">0</span>
                    </div>
                    <div class="summary-row total">
                        <span>TOTAL:</span>
                        <span id="total-entradas">S/ 0.00</span>
                    </div>
                </div>
            </div>
            <div class="footer">
                <button class="btn-secondary" onclick="goToScreen('butacas')">← Atrás</button>
                <button class="btn-primary" onclick="goToScreen('confiteria')" id="btn-continuar-tarifas" disabled>
                    Continuar →
                </button>
            </div>
        </div>

        <!-- Pantalla de Confitería -->
        <div id="screen-confiteria" class="screen">
            <div class="header">
                <div class="logo-small">CINERAMA</div>
                <h2>Confitería (Opcional)</h2>
            </div>
            <div class="content">
                <div id="confiteria-grid" class="confiteria-grid"></div>
                <div class="confiteria-summary">
                    <div class="summary-row total">
                        <span>TOTAL CONFITERÍA:</span>
                        <span id="total-confiteria">S/ 0.00</span>
                    </div>
                </div>
            </div>
            <div class="footer">
                <button class="btn-secondary" onclick="goToScreen('tarifas')">← Atrás</button>
                <button class="btn-primary" onclick="goToScreen('facturacion')">
                    Continuar →
                </button>
            </div>
        </div>

        <!-- Pantalla de Facturación -->
        <div id="screen-facturacion" class="screen">
            <div class="header">
                <div class="logo-small">CINERAMA</div>
                <h2>Datos de Facturación</h2>
            </div>
            <div class="content">
                <div class="facturacion-form">
                    <div class="tipo-comprobante">
                        <label>
                            <input type="radio" name="comprobante" value="boleta" checked onchange="cambiarComprobante()">
                            <span class="radio-label">Boleta</span>
                        </label>
                        <label>
                            <input type="radio" name="comprobante" value="factura" onchange="cambiarComprobante()">
                            <span class="radio-label">Factura</span>
                        </label>
                    </div>

                    <div id="form-boleta" class="form-comprobante active">
                        <div class="form-group">
                            <label>DNI:</label>
                            <input type="text" id="dni" maxlength="8" placeholder="Ingrese DNI">
                        </div>
                        <div class="form-group">
                            <label>Nombre:</label>
                            <input type="text" id="nombre-boleta" placeholder="Nombre completo">
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" id="email-boleta" placeholder="correo@ejemplo.com">
                        </div>
                    </div>

                    <div id="form-factura" class="form-comprobante">
                        <div class="form-group">
                            <label>RUC:</label>
                            <input type="text" id="ruc" maxlength="11" placeholder="Ingrese RUC">
                        </div>
                        <div class="form-group">
                            <label>Razón Social:</label>
                            <input type="text" id="razon-social" placeholder="Razón social de la empresa">
                        </div>
                        <div class="form-group">
                            <label>Dirección Fiscal:</label>
                            <input type="text" id="direccion-fiscal" placeholder="Dirección fiscal">
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" id="email-factura" placeholder="correo@empresa.com">
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer">
                <button class="btn-secondary" onclick="goToScreen('confiteria')">← Atrás</button>
                <button class="btn-primary" onclick="goToScreen('pago')">
                    Continuar al Pago →
                </button>
            </div>
        </div>

        <!-- Pantalla de Pago -->
        <div id="screen-pago" class="screen">
            <div class="header">
                <div class="logo-small">CINERAMA</div>
                <h2>Resumen y Pago</h2>
            </div>
            <div class="content">
                <div class="pago-container">
                    <div class="resumen-compra">
                        <h3>Resumen de tu Compra</h3>
                        <div id="resumen-detalle"></div>
                        <div class="total-final">
                            <span>TOTAL A PAGAR:</span>
                            <span id="total-final">S/ 0.00</span>
                        </div>
                    </div>

                    <div class="pinpad-container">
                        <div class="pinpad">
                            <div class="pinpad-screen">
                                <div id="pinpad-mensaje">Inserte o acerque su tarjeta</div>
                                <div id="pinpad-monto"></div>
                            </div>
                            <div class="pinpad-animation" id="pinpad-animation">
                                <div class="card-slot"></div>
                                <div class="contactless">
                                    <div class="wave"></div>
                                    <div class="wave"></div>
                                    <div class="wave"></div>
                                </div>
                            </div>
                            <button class="btn-primary btn-pagar" onclick="procesarPago()">
                                PROCESAR PAGO
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer">
                <button class="btn-secondary" onclick="goToScreen('facturacion')">← Atrás</button>
            </div>
        </div>

        <!-- Pantalla de Confirmación -->
        <div id="screen-confirmacion" class="screen">
            <div class="confirmacion-container">
                <div class="success-icon">✓</div>
                <h1>¡Compra Exitosa!</h1>
                <div id="codigo-compra" class="codigo-compra"></div>
                <div class="mensaje-confirmacion">
                    <p>Se ha enviado la confirmación a tu correo electrónico</p>
                    <p>Presenta este código en la entrada del cine</p>
                </div>
                <button class="btn-primary" onclick="reiniciarKiosko()">
                    REALIZAR NUEVA COMPRA
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>

</html>