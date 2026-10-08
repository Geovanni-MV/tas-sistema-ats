<?php
namespace App\DTO\Reclutamiento;
/**
 * DTO inmutable que representa los datos transfericos de la entidad de reclutamiento.empresas
 */
final readonly class EmpresaDTO
{
    public function __construct(
        public ?int $id_empresa,
        public string $nombre_empresa,
        public ?string $img_logo_url
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            id_empresa: isset($data['id_empresa']) ? (int) $data['id_empresa'] : null,
            nombre_empresa: isset($data['nombre_empresa']) && trim((string) $data['nombre_empresa']) !== '' 
                            ? trim((string) $data['nombre_empresa']) : null,
            img_logo_url: isset($data['img_url_logo']) && trim((string) $data['img_url_logo']) !== '' 
                            ? trim((string) $data['img_url_logo']) : null
        );
    }
}
?>