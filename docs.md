products:{
    user_id

    type: physical/digital/subscription
    name, 
    category
    short_description
    description 
    product_price,
    bargaining_price,
    is_discount_offer: inactive/lifetime/limited
    is_discount_type: inactive/fixed/percentage
    discount_value: ,
    discount_start_at: 
    discount_end_at: 

    #Publishing
    publish_type: draft/immediatly
    publish_at: date & time
}

product_media_items{
    product_id:-
    cover: image
    
    is_slider: true/false
    media_items: [
        {
            'media_type': upload_video/yt_video/image
            'source_url': 
        }
    ]
}

product_inventory{
    product_id:-
    
    shipping_profile:
    is_variants: true/false
    available_qty:  is_variants == true ? null : val
    stock_alert: is_variants == true ? null : val
    weight:
}
product_variants{
    product_id:-
    have_size: true/false
    size: 
    have_color: true/fasle
    color:
    qty:
    alert_qty:
    weight:
}

product_discoverability{
    product_id
    tags
    slug
    meta_title
    meta_description
    seo_tags
}
product_downloadable{
    product_id
    access_type:
    drive_link
    access_instruction:
}
product_subscriptions{
    product_id
    email
    number
    username
    password
    is_authenticated
}