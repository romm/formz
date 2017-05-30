config.tx_extbase {
    persistence {
        classes {
            Romm\Formz\Domain\Model\FormMetadata {
                mapping {
                    columns {
                        hash.mapOnProperty = hash
                        class_name.mapOnProperty = className
                        identifier.mapOnProperty = identifier
                        metadata.mapOnProperty = metadata
                    }
                }
            }
        }
    }
}
