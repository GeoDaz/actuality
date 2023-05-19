// modules
import React from 'react';
import { Link } from 'react-router-dom';
import { Card, Button } from 'semantic-ui-react';
import { formatDate } from '../../functions';
import ScrollReveal from '../ScrollReveal';
import Tag from './Tag';

const ActualityTeaser = ({ actuality, TitleAs = 'h3', btnProps = {} }) => (
	<ScrollReveal className="ui card actuality-teaser" animation="zoomIn" earlier>
		<Card.Content>
			<TitleAs className="text-center">
				<Link to={`/entity/actualities/${actuality.id}`}>{actuality.title}</Link>
			</TitleAs>
			<div className="image mb-3">
				<Link to={`/entity/actualities/${actuality.id}`}>
					<img
						className="img-fluid d-block"
						src={
							actuality.images && actuality.images.length
								? `/images/actualities/375px/${actuality.images[0]}`
								: '/images/default_actuality_colored.jpg'
						}
						onError={e => {
							e.target.onerror = null;
							e.target.src = '/images/default_actuality_colored.jpg';
						}}
						alt={actuality.alt}
						// TODO gerer une taille fixe
					/>
					<span className="sr-only">
						Illustration de l'actualité : {actuality.title}
					</span>
				</Link>
			</div>
			{actuality.tags.length > 0 && (
				<div className="mb-2 text-center">
					{actuality.tags.map((tag, i) => (
						<Tag key={i} tag={tag} />
					))}
				</div>
			)}
			<p className="date mb-3">
				{formatDate(actuality.createdAt)}
			</p>
			{!!actuality.shortDescription && (
				<p className="short-description">{actuality.shortDescription}</p>
			)}
		</Card.Content>
		<Card.Content>
			<Button
				color="red"
				{...btnProps}
				as={Link}
				to={`/entity/actualities/${actuality.id}`}
			>
				Lire la suite
			</Button>
		</Card.Content>
	</ScrollReveal>
);
export default ActualityTeaser;
